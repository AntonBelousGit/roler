<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use DatabaseMigrations;

    public function testSimpleUserCannotAccessCategories()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('categories');
        $response->assertStatus(403);
    }

    public function testAdminUserCanAccessCategories()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get('categories');
        $response->assertStatus(200);
    }

    public function testPublisherUserCannotAccessCategories()
    {
        $publisher = User::factory()->create(['role_id' => 3]);

        $response = $this->actingAs($publisher)->get('categories');
        $response->assertStatus(403);
    }

    public function testUserCannotSeeUserColumnInArticleTable()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('articles');
        $response->assertDontSee('User');
    }

    public function testAdminCanSeeUserColumnInArticleTable()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get('articles');
        $response->assertSee('User');
    }

    public function testUserCannotSeePublishedCheckboxInCreateArticleForm()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('articles/create');
        $response->assertDontSee('Published');
    }

    public function testAdminCanSeePublishedCheckboxInCreateArticleForm()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get('articles/create');
        $response->assertSee('Published');
    }

    public function testPublisherCanSeePublishedCheckboxInCreateArticleForm()
    {
        $publisher = User::factory()->create(['role_id' => 3]);

        $response = $this->actingAs($publisher)->get('articles/create');
        $response->assertSee('Published');
    }

    public function testUserCannotSeePublishedCheckboxInEditArticleForm()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('articles/' . $article->id . '/edit');
        $response->assertDontSee('Published');
    }

    public function testAdminCanSeePublishedCheckboxInEditArticleForm()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $article = Article::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get('articles/' . $article->id . '/edit');
        $response->assertSee('Published');
    }

    public function testPublisherCanSeePublishedCheckboxInEditArticleForm()
    {
        $publisher = User::factory()->create(['role_id' => 3]);
        $article = Article::factory()->create(['user_id' => $publisher->id]);

        $response = $this->actingAs($publisher)->get('articles/' . $article->id . '/edit');
        $response->assertSee('Published');
    }

    public function testUserCannotPublishArticle()
    {
        $user = User::factory()->create();

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text', 'published' => 1];
        $response = $this->actingAs($user)->post('articles', $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);

        $response = $this->actingAs($user)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);
    }

    public function testPublisherCanSaveAndNotPublishArticle()
    {
        $publisher = User::factory()->create(['role_id' => 3]);

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text'];
        $response = $this->actingAs($publisher)->post('articles', $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);

        $response = $this->actingAs($publisher)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);
    }

    public function testAdminCanSaveAndNotPublishArticle()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text'];
        $response = $this->actingAs($admin)->post('articles', $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);

        $response = $this->actingAs($admin)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);
    }

    public function testPublisherCanPublishAndUnpublishArticle()
    {
        $publisher = User::factory()->create(['role_id' => 3]);

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text', 'published' => 1];
        $response = $this->actingAs($publisher)->post('articles', $articleData);
        $response->assertRedirect();


        $article = Article::firstOrFail();
        $this->assertNotNull($article->published_at);

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text'];
        $response = $this->actingAs($publisher)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);
    }

    public function testAdminCanPublishAndUnpublishArticle()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text', 'published' => 1];
        $response = $this->actingAs($admin)->post('articles', $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNotNull($article->published_at);

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text'];
        $response = $this->actingAs($admin)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);
    }

}
