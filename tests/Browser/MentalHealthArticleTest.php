<?php

namespace Tests\Browser;

use App\Models\Article;
use App\Models\ArticleCategory;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MentalHealthArticleTest extends DuskTestCase
{
    // PBI-21 Positive
    public function test_pbi21_positive_student_can_search_and_read_article(): void
    {
        $category = ArticleCategory::firstOrCreate(['category_name' => 'Anxiety']);
        Article::firstOrCreate(
            ['title' => 'Understanding anxious thoughts'],
            ['category_id' => $category->id, 'content' => 'Anxiety is a natural stress response, but persistent worry can affect your focus, sleep, and daily routine.']
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/learn')
                    ->type('search', 'anxious')
                    ->press('Search')
                    ->assertSee('Understanding anxious thoughts')
                    ->clickLink('Read article >')
                    ->assertSee('Anxiety is a natural stress response');
        });
    }

    // PBI-21 Negative
    public function test_pbi21_negative_unmatched_search_shows_empty_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/learn')
                    ->type('search', 'nonexistent_keyword_xyz999')
                    ->press('Search')
                    ->assertSee('No articles found');
        });
    }

    // PBI-22 Positive
    public function test_pbi22_positive_student_can_save_and_view_saved_article(): void
    {
        $category = ArticleCategory::firstOrCreate(['category_name' => 'Sleep']);
        $article = Article::firstOrCreate(
            ['title' => 'A practical sleep routine'],
            ['category_id' => $category->id, 'content' => 'Sleep hygiene steps.']
        );

        // Ensure clean unsaved state before testing save
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'student@mindlink.test'],
            ['name' => 'Test Student', 'password' => bcrypt('password')]
        );
        $user->savedArticles()->detach($article->id);

        $this->browse(function (Browser $browser) use ($article) {
            $browser->visit('/learn/' . $article->id)
                    ->waitForText('Save Article ☆')
                    ->press('Save Article ☆')
                    ->waitForText('Article saved for later.')
                    ->assertSee('Article saved for later.')
                    ->visit('/learn/saved')
                    ->waitForText('A practical sleep routine')
                    ->assertSee('A practical sleep routine');
        });
    }

    // PBI-22 Negative (Unsaving removes it from saved list)
    public function test_pbi22_negative_student_can_unsave_article(): void
    {
        $category = ArticleCategory::firstOrCreate(['category_name' => 'Sleep']);
        $article = Article::firstOrCreate(
            ['title' => 'A practical sleep routine'],
            ['category_id' => $category->id, 'content' => 'Sleep hygiene steps.']
        );

        
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'student@mindlink.test'],
            ['name' => 'Test Student', 'password' => bcrypt('password')]
        );
        $user->savedArticles()->syncWithoutDetaching([$article->id => ['saved_at' => now()]]);

        $this->browse(function (Browser $browser) use ($article) {
            $browser->visit('/learn/' . $article->id)
                    ->waitForText('Saved ★ (Click to remove)')
                    ->press('Saved ★ (Click to remove)')
                    ->waitForText('Article removed from saved list.')
                    ->assertSee('Article removed from saved list.')
                    ->visit('/learn/saved')
                    ->waitForText('No saved articles yet')
                    ->assertSee('No saved articles yet');
        });
    }

    // PBI-23 Positive
    public function test_pbi23_positive_admin_can_create_article(): void
    {
        $category = ArticleCategory::firstOrCreate(['category_name' => 'Academic stress']);

        $this->browse(function (Browser $browser) use ($category) {
            $browser->visit('/admin/articles/create')
                    ->type('title', 'Exam Stress Guide')
                    ->select('category_id', $category->id)
                    ->type('content', 'Tips for managing exam pressure.')
                    ->press('Publish Article')
                    ->assertSee('Article created successfully.')
                    ->assertSee('Exam Stress Guide');
        });
    }

    // PBI-23 Negative
    public function test_pbi23_negative_admin_cannot_submit_empty_article(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/articles/create')
                    ->script([
                        "document.querySelector('input[name=\"title\"]').removeAttribute('required');",
                        "document.querySelector('textarea[name=\"content\"]').removeAttribute('required');"
                    ]);

            $browser->press('Publish Article')
                    ->assertPathIs('/admin/articles/create')
                    ->assertSee('The title field is required.')
                    ->assertSee('The content field is required.');
        });
    }
}
