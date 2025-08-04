<?php

namespace JobMetric\Reaction\Tests\Feature;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use JobMetric\Reaction\Events\ReactionAddEvent;
use JobMetric\Reaction\Events\ReactionRemovedEvent;
use JobMetric\Reaction\Events\ReactionRemovingEvent;
use JobMetric\Reaction\Exceptions\InvalidReactionSourceException;
use JobMetric\Reaction\Models\Reaction;
use JobMetric\Reaction\Tests\Stubs\Models\Article;
use JobMetric\Reaction\Tests\Stubs\Models\User;
use JobMetric\Reaction\Tests\TestCase as BaseTestCase;
use Throwable;

class HasReactionTest extends BaseTestCase
{
    /**
     * @throws Throwable
     */
    public function test_article_trait_relationship()
    {
        $article = new Article;

        $this->assertInstanceOf(MorphMany::class, $article->reactions());
    }

    /**
     * @throws Throwable
     */
    public function test_add_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        Event::fake();

        $reaction = $article->addReaction('like', $user);

        $this->assertInstanceOf(Reaction::class, $reaction);

        $this->assertEquals('like', $reaction->reaction);
        $this->assertEquals($user->id, $reaction->reactor_id);
        $this->assertEquals(User::class, $reaction->reactor_type);

        $this->assertDatabaseHas('reactions', [
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
            'reaction' => 'like',
        ]);

        // Check if the event was dispatched
        Event::assertDispatched(ReactionAddEvent::class);

        // Check if the reaction is retrievable from the article
        $reactions = $article->reactions()->get();

        $this->assertInstanceOf(Collection::class, $reactions);
        $this->assertCount(1, $reactions);

        $this->assertEquals($reaction->id, $reactions->first()->id);
        $this->assertEquals($reaction->reactor_type, $reactions->first()->reactor_type);
        $this->assertEquals($reaction->reactor_id, $reactions->first()->reactor_id);
        $this->assertEquals(Article::class, $reactions->first()->reactable_type);
        $this->assertEquals($article->id, $reactions->first()->reactable_id);
        $this->assertEquals($reaction->reaction, $reactions->first()->reaction);
        $this->assertEquals($reaction->ip, $reactions->first()->ip);
        $this->assertEquals($reaction->device_id, $reactions->first()->device_id);
        $this->assertEquals($reaction->source, $reactions->first()->source);
    }

    public function test_add_reaction_with_reactor_only_should_pass()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $this->assertDatabaseHas('reactions', [
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'reaction' => 'like',
        ]);
    }

    public function test_add_reaction_with_device_id_only_should_pass()
    {
        $article = Article::factory()->create();

        $request = Request::create('/fake-url', 'POST', [], [], [], [
            'HTTP_' . strtoupper(str_replace('-', '_', config('reaction.headers.device_id'))) => 'test-device-id',
        ]);
        app()->instance('request', $request);

        $article->addReaction('like');

        $this->assertDatabaseHas('reactions', [
            'device_id' => 'test-device-id',
            'reaction' => 'like',
        ]);
    }

    public function test_add_reaction_without_reactor_or_device_should_throw_exception()
    {
        $this->expectException(InvalidReactionSourceException::class);

        $article = Article::factory()->create();

        $article->addReaction('like');
    }

    /**
     * @throws Throwable
     */
    public function test_add_reaction_without_reactor()
    {
        $article = Article::factory()->create();

        Event::fake();

        $reaction = $article->addReaction('like');

        $this->assertInstanceOf(Reaction::class, $reaction);

        $this->assertEquals('like', $reaction->reaction);
        $this->assertNull($reaction->reactor_id);
        $this->assertNull($reaction->reactor_type);

        $this->assertDatabaseHas('reactions', [
            'reactor_type' => null,
            'reactor_id' => null,
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
            'reaction' => 'like',
        ]);

        // Check if the event was dispatched
        Event::assertDispatched(ReactionAddEvent::class);

        // Check if the reaction is retrievable from the article
        $reactions = $article->reactions()->get();

        $this->assertInstanceOf(Collection::class, $reactions);
        $this->assertCount(1, $reactions);

        $this->assertEquals($reaction->id, $reactions->first()->id);
        $this->assertNull($reactions->first()->reactor_type);
        $this->assertNull($reactions->first()->reactor_id);
        $this->assertEquals(Article::class, $reactions->first()->reactable_type);
        $this->assertEquals($article->id, $reactions->first()->reactable_id);
        $this->assertEquals($reaction->reaction, $reactions->first()->reaction);
    }

    public function test_add_reaction_with_source()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $reaction = $article->addReaction('like', $user, ['source' => 'web']);

        $this->assertEquals('web', $reaction->source);

        $this->assertDatabaseHas('reactions', [
            'source' => 'web',
            'reaction' => 'like',
        ]);
    }

    public function test_add_reaction_with_ip()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $reaction = $article->addReaction('like', $user, ['ip' => '192.168.1.0']);
        $this->assertEquals('192.168.1.0', $reaction->ip);

        $this->assertDatabaseHas('reactions', [
            'ip' => '192.168.1.0',
            'reaction' => 'like',
        ]);
    }

    public function test_add_reaction_with_device_id()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $reaction = $article->addReaction('like', $user, ['device_id' => 'test-device-id']);
        $this->assertEquals('test-device-id', $reaction->device_id);

        $this->assertDatabaseHas('reactions', [
            'device_id' => 'test-device-id',
            'reaction' => 'like',
        ]);
    }

    public function test_remove_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $reaction = $article->addReaction('like', $user);

        Event::fake();

        $deleted = $article->removeReaction('like', $user);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('reactions', [
            'id' => $reaction->id,
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
            'reaction' => 'like',
        ]);

        // Check if the event was dispatched
        Event::assertDispatched(ReactionRemovingEvent::class);
        Event::assertDispatched(ReactionRemovedEvent::class);
    }

    public function test_remove_reaction_with_device_id()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $reaction = $article->addReaction('like', $user, ['device_id' => 'test-device-id']);

        Event::fake();

        $deleted = $article->removeReaction('like', $user, 'test-device-id');

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('reactions', [
            'id' => $reaction->id,
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'device_id' => 'test-device-id',
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
            'reaction' => 'like',
        ]);

        // Check if the event was dispatched
        Event::assertDispatched(ReactionRemovingEvent::class);
        Event::assertDispatched(ReactionRemovedEvent::class);
    }

    public function test_toggle_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        Event::fake();

        // Add reaction
        $reaction = $article->toggleReaction('like', $user);

        $this->assertInstanceOf(Reaction::class, $reaction);
        $this->assertEquals('like', $reaction->reaction);
        $this->assertEquals($user->id, $reaction->reactor_id);
        $this->assertEquals(User::class, $reaction->reactor_type);

        // Check if the event was dispatched
        Event::assertDispatched(ReactionAddEvent::class);

        // Remove reaction
        $deleted = $article->toggleReaction('like', $user);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('reactions', [
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
            'reaction' => 'like',
        ]);

        // Check if the event was dispatched
        Event::assertDispatched(ReactionRemovingEvent::class);
        Event::assertDispatched(ReactionRemovedEvent::class);
    }

    public function test_remove_all_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add multiple reactions
        $article->addReaction('like', $user);
        $article->addReaction('dislike', $user);

        // Remove all reactions
        $deleted = $article->removeAllReactions($user);

        $this->assertGreaterThan(0, $deleted);
        $this->assertSoftDeleted('reactions', [
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
        ]);
    }

    public function test_restore_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add a reaction
        $reaction = $article->addReaction('like', $user);

        // Remove the reaction
        $article->removeReaction('like', $user);

        // Restore the reaction
        $restored = $reaction->restore();

        $this->assertTrue($restored);
        $this->assertDatabaseHas('reactions', [
            'id' => $reaction->id,
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
            'reaction' => 'like',
            'deleted_at' => null, // Ensure it's not soft deleted
        ]);
    }

    public function test_update_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add a reaction
        $reaction = $article->addReaction('like', $user);

        // Update the reaction
        $updatedReaction = $article->updateReaction('like', 'love', $user);

        $this->assertInstanceOf(Reaction::class, $updatedReaction);
        $this->assertEquals('love', $updatedReaction->reaction);
        $this->assertEquals($user->id, $updatedReaction->reactor_id);
        $this->assertEquals(User::class, $updatedReaction->reactor_type);

        // Check if the reaction was updated in the database
        $this->assertDatabaseHas('reactions', [
            'id' => $reaction->id,
            'reaction' => 'love',
        ]);
    }

    public function test_has_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add a reaction
        $article->addReaction('like', $user);

        // Check if the reaction exists
        $hasReaction = $article->hasReaction('like', $user);

        $this->assertTrue($hasReaction);

        // Check for a reaction that does not exist
        $hasReaction = $article->hasReaction('dislike', $user);

        $this->assertFalse($hasReaction);
    }

    public function test_total_reactions()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add multiple reactions
        $article->addReaction('like', $user);
        $article->addReaction('dislike', $user);

        // Count total reactions
        $totalReactions = $article->totalReactions();

        $this->assertEquals(1, $totalReactions);

        // Check if the reactions are counted correctly
        $this->assertDatabaseCount('reactions', 1);
    }

    public function test_count_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add multiple reactions
        $article->addReaction('like', $user);
        $article->addReaction('dislike', $user);

        // Count reactions of type 'like'
        $countLike = $article->countReactions('like');
        $this->assertEquals(0, $countLike);

        // Count reactions of type 'dislike'
        $countDislike = $article->countReactions('dislike');
        $this->assertEquals(1, $countDislike);
    }

    public function test_reaction_summary()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add multiple reactions
        $article->addReaction('like', $user);
        $article->addReaction('dislike', $user);

        // Get reaction summary
        $summary = $article->reactionSummary();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $summary);

        $this->assertArrayNotHasKey('like', $summary->toArray());
        $this->assertArrayHasKey('dislike', $summary->toArray());
        $this->assertEquals(1, $summary->get('dislike'));
    }

    public function test_latest_reaction()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        // Add multiple reactions
        $article->addReaction('like', $user);
        $article->addReaction('dislike', $user);

        // Get latest reactions
        $latestReactions = $article->latestReactions(2);

        $this->assertInstanceOf(Collection::class, $latestReactions);
        $this->assertCount(1, $latestReactions);

        // Check if the latest reactions are returned correctly
        foreach ($latestReactions as $reaction) {
            $this->assertInstanceOf(Reaction::class, $reaction);
            $this->assertEquals($article->id, $reaction->reactable_id);
            $this->assertEquals(Article::class, $reaction->reactable_type);
        }
    }
}
