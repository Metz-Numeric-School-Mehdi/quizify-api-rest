<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tag;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Category;
use App\Models\QuizLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QuizLevelSeeder;

/**
 * Tag model unit tests.
 *
 * This test class verifies the core functionality of the Tag model,
 * including creation, relationships with quizzes, and tagging operations.
 */
class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            CategorySeeder::class,
            QuizLevelSeeder::class,
        ]);
    }

    /**
     * Test creating a tag.
     *
     * @test
     */
    public function it_creates_a_tag()
    {
        $tag = Tag::create([
            'name' => 'Laravel',
            'description' => 'Tag for Laravel related quizzes',
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Laravel',
            'description' => 'Tag for Laravel related quizzes',
        ]);

        $this->assertInstanceOf(Tag::class, $tag);
    }

    /**
     * Test tag can be associated with multiple quizzes.
     *
     * @test
     */
    public function it_can_be_associated_with_multiple_quizzes()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $tag = Tag::create([
            'name' => 'PHP',
            'description' => 'PHP programming tag',
        ]);

        $quiz1 = Quiz::create([
            'title' => 'PHP Basics Quiz',
            'slug' => 'php-basics-quiz',
            'description' => 'Basic PHP concepts quiz',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $quiz2 = Quiz::create([
            'title' => 'Advanced PHP Quiz',
            'slug' => 'advanced-php-quiz',
            'description' => 'Advanced PHP concepts quiz',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 45,
            'pass_score' => 80,
            'category_id' => $category->id,
        ]);

        // Associate tag with quizzes
        $quiz1->tags()->attach($tag->id);
        $quiz2->tags()->attach($tag->id);

        // Verify tag is associated with both quizzes
        $associatedQuizzes = $tag->quizzes()->count();
        $this->assertEquals(2, $associatedQuizzes);
    }

    /**
     * Test updating tag attributes.
     *
     * @test
     */
    public function it_can_update_tag_attributes()
    {
        $tag = Tag::create([
            'name' => 'Original Tag',
            'description' => 'Original tag description',
        ]);

        $tag->update([
            'name' => 'Updated Tag',
            'description' => 'Updated tag description',
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag',
            'description' => 'Updated tag description',
        ]);
    }

    /**
     * Test tag name is required.
     *
     * @test
     */
    public function it_requires_name_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Tag::create([
            'description' => 'Tag without name',
        ]);
    }

    /**
     * Test finding tag by name.
     *
     * @test
     */
    public function it_can_find_tag_by_name()
    {
        $tag = Tag::create([
            'name' => 'Unique Tag Name',
            'description' => 'Searchable tag',
        ]);

        $found = Tag::where('name', 'Unique Tag Name')->first();

        $this->assertNotNull($found);
        $this->assertEquals($tag->id, $found->id);
        $this->assertEquals('Unique Tag Name', $found->name);
    }

    /**
     * Test tag name should be unique.
     *
     * @test
     */
    public function it_enforces_unique_tag_names()
    {
        Tag::create([
            'name' => 'unique_tag',
            'description' => 'First tag with this name',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Tag::create([
            'name' => 'unique_tag',
            'description' => 'Second tag with same name',
        ]);
    }

    /**
     * Test many-to-many relationship with quizzes.
     *
     * @test
     */
    public function it_has_many_to_many_relationship_with_quizzes()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $tag1 = Tag::create(['name' => 'JavaScript', 'description' => 'JS tag']);
        $tag2 = Tag::create(['name' => 'Frontend', 'description' => 'Frontend tag']);

        $quiz = Quiz::create([
            'title' => 'JavaScript Frontend Quiz',
            'slug' => 'js-frontend-quiz',
            'description' => 'Quiz about JavaScript and Frontend',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        // Associate multiple tags with quiz
        $quiz->tags()->attach([$tag1->id, $tag2->id]);

        // Verify quiz has both tags
        $quizTags = $quiz->tags()->count();
        $this->assertEquals(2, $quizTags);

        // Verify tags have the quiz
        $this->assertEquals(1, $tag1->quizzes()->count());
        $this->assertEquals(1, $tag2->quizzes()->count());
    }

    /**
     * Test detaching tags from quizzes.
     *
     * @test
     */
    public function it_can_detach_tags_from_quizzes()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $tag = Tag::create([
            'name' => 'Temporary Tag',
            'description' => 'Tag to be detached',
        ]);

        $quiz = Quiz::create([
            'title' => 'Temporary Quiz',
            'slug' => 'temporary-quiz',
            'description' => 'Quiz for detaching test',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        // Attach tag
        $quiz->tags()->attach($tag->id);
        $this->assertEquals(1, $quiz->tags()->count());

        // Detach tag
        $quiz->tags()->detach($tag->id);
        $this->assertEquals(0, $quiz->tags()->count());
    }

    /**
     * Test finding quizzes by tag.
     *
     * @test
     */
    public function it_can_find_quizzes_by_tag()
    {
        $user = User::factory()->create();
        $category = Category::first();
        $level = QuizLevel::first();

        $tag = Tag::create([
            'name' => 'Search Tag',
            'description' => 'Tag for search testing',
        ]);

        $quiz1 = Quiz::create([
            'title' => 'Tagged Quiz 1',
            'slug' => 'tagged-quiz-1',
            'description' => 'First quiz with search tag',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 30,
            'pass_score' => 70,
            'category_id' => $category->id,
        ]);

        $quiz2 = Quiz::create([
            'title' => 'Tagged Quiz 2',
            'slug' => 'tagged-quiz-2',
            'description' => 'Second quiz with search tag',
            'is_public' => true,
            'level_id' => $level->id,
            'status' => 'published',
            'user_id' => $user->id,
            'duration' => 45,
            'pass_score' => 80,
            'category_id' => $category->id,
        ]);

        // Tag both quizzes
        $quiz1->tags()->attach($tag->id);
        $quiz2->tags()->attach($tag->id);

        // Find quizzes by tag
        $taggedQuizzes = $tag->quizzes;
        $this->assertCount(2, $taggedQuizzes);
        $this->assertTrue($taggedQuizzes->contains($quiz1));
        $this->assertTrue($taggedQuizzes->contains($quiz2));
    }
}
