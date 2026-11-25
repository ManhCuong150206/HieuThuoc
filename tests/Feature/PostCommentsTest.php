<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_and_reply_to_the_exact_article_comment(): void
    {
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $customer = User::factory()->create();
        $post = $admin->posts()->create([
            'title' => 'Bài viết có bình luận',
            'content' => 'Nội dung bài viết',
            'status' => 'PUBLISHED',
            'published_at' => today(),
        ]);
        $storeUrl = route('posts.comments.store', $post);
        $this->post($storeUrl, ['body' => 'Bình luận khách'])->assertRedirect(route('login'));

        $this->actingAs($customer->account)->post($storeUrl, ['body' => ' '])
            ->assertSessionHasErrorsIn('postComment', 'body');
        $response = $this->post($storeUrl, ['body' => 'Bình luận khách', 'admin_reply' => 'Giả danh']);
        $comment = $post->comments()->sole();
        $this->assertNull($comment->admin_reply);
        $viewUrl = route('posts.show', ['post' => $post, 'comment' => $comment->id]).'#binh-luan-'.$comment->id;
        $response->assertRedirect($viewUrl);

        $post->comments()->createMany(array_fill(0, 10, ['account_id' => $customer->account->id, 'body' => 'Bình luận mới']));
        $this->get(route('posts.show', $post))->assertDontSee('Bình luận khách');
        $replyUrl = route('admin.posts.comments.reply', [$post, $comment]);
        $this->get($viewUrl)->assertOk()->assertSee('Bình luận khách')->assertDontSee($replyUrl);
        $this->put($replyUrl, ['admin_reply' => 'Không được lưu'])->assertForbidden();

        $otherPost = $admin->posts()->save(new Post($post->only('title', 'content', 'status', 'published_at')));
        $adminList = route('admin.reviews.index', ['tab' => 'bai-viet']);
        $this->actingAs($admin)->get($adminList)->assertOk()
            ->assertSee($post->title)->assertSee($customer->full_name)->assertSee($viewUrl);
        $this->get($viewUrl)->assertOk()->assertSee($replyUrl);
        $this->put(route('admin.posts.comments.reply', [$otherPost, $comment]), ['admin_reply' => 'Sai bài viết'])->assertNotFound();
        $this->put($replyUrl, ['admin_reply' => str_repeat('a', 501)])->assertRedirect($viewUrl)
            ->assertSessionHasErrorsIn('reply'.$comment->id, 'admin_reply');
        $this->put($replyUrl, ['admin_reply' => 'Cảm ơn bạn đã bình luận.'])->assertRedirect($viewUrl);

        $this->actingAs($customer->account)->get($viewUrl)->assertOk()->assertSee('Phản hồi từ An Tâm Pharmacy')
            ->assertSee('Cảm ơn bạn đã bình luận.')->assertDontSee($replyUrl);
        $this->actingAs($admin)->get($adminList)->assertSee('Đã trả lời');
    }

    public function test_ajax_comments_return_updated_markup_and_admin_replies_return_json(): void
    {
        $admin = Account::factory()->create(['role' => 'ADMIN']);
        $customer = User::factory()->create();
        $post = $admin->posts()->create([
            'title' => 'Bài viết có bình luận AJAX',
            'content' => 'Nội dung bài viết',
            'status' => 'PUBLISHED',
            'published_at' => today(),
        ]);
        $storeUrl = route('posts.comments.store', $post);
        $this->postJson($storeUrl, ['body' => 'Khách chưa đăng nhập'])->assertUnauthorized();
        $this->actingAs($customer->account)->postJson($storeUrl, ['body' => ' '])
            ->assertUnprocessable()->assertJsonValidationErrors('body');
        $post->comments()->createMany(array_fill(0, 10, ['account_id' => $customer->account_id, 'body' => 'Bình luận trước đó']));

        $response = $this->postJson($storeUrl, ['body' => 'Bình luận AJAX <b>mới</b>'])
            ->assertCreated()->assertJsonPath('total', 11);
        $comment = $post->comments()->latest('id')->firstOrFail();
        $replyUrl = route('admin.posts.comments.reply', [$post, $comment]);
        $response->assertJsonPath('url', route('posts.show', ['post' => $post, 'comment' => $comment->id]).'#binh-luan-'.$comment->id);
        $this->assertStringContainsString('Bình luận AJAX &lt;b&gt;mới&lt;/b&gt;', $response->json('html'));
        $this->assertStringContainsString(route('posts.show', ['post' => $post, 'page' => 2]).'#binh-luan', $response->json('html'));
        $this->assertStringNotContainsString($replyUrl, $response->json('html'));

        $this->putJson($replyUrl, ['admin_reply' => 'Khách trả lời'])->assertForbidden();
        $this->actingAs($admin)->putJson($replyUrl, ['admin_reply' => str_repeat('a', 501)])
            ->assertUnprocessable()->assertJsonValidationErrors('admin_reply');
        $this->post($replyUrl, ['_method' => 'PUT', 'admin_reply' => 'Phản hồi AJAX'], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('reply', 'Phản hồi AJAX');
        $this->putJson($replyUrl, ['admin_reply' => 'Đã sửa phản hồi'])
            ->assertOk()->assertJsonPath('reply', 'Đã sửa phản hồi');
        $this->assertSame('Đã sửa phản hồi', $comment->fresh()->admin_reply);
    }
}
