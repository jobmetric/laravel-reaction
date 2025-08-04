<?php

namespace JobMetric\Reaction\Tests\Feature;

use Illuminate\Database\Eloquent\Collection;
use JobMetric\Reaction\Models\Reaction;
use JobMetric\Reaction\Tests\Stubs\Models\Article;
use JobMetric\Reaction\Tests\Stubs\Models\User;
use JobMetric\Reaction\Tests\TestCase as BaseTestCase;

class CanReactTest extends BaseTestCase
{
    public function test_reactions_given_relationship()
    {
        $user = new User;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $user->reactionsGiven());
    }

    public function test_has_reacted_to()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $this->assertFalse($user->hasReactedTo($article));

        $article->addReaction('like', $user);

        $this->assertTrue($user->hasReactedTo($article));
        $this->assertTrue($user->hasReactedTo($article, 'like'));
        $this->assertFalse($user->hasReactedTo($article, 'dislike'));
    }

    public function test_reacted_with_to()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $this->assertTrue($user->reactedWithTo('like', $article));
        $this->assertFalse($user->reactedWithTo('dislike', $article));
    }

    public function test_reaction_to()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $reaction = $user->reactionTo($article);

        $this->assertInstanceOf(Reaction::class, $reaction);
        $this->assertEquals('like', $reaction->reaction);
    }

    public function test_remove_reaction_from()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $this->assertTrue($user->removeReactionFrom($article));

        $this->assertSoftDeleted('reactions', [
            'reactor_type' => User::class,
            'reactor_id' => $user->id,
            'reactable_type' => Article::class,
            'reactable_id' => $article->id,
        ]);
    }

    public function test_count_reaction_made()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $this->assertEquals(1, $user->countReactionMade('like'));
        $this->assertEquals(0, $user->countReactionMade('dislike'));
    }

    public function test_total_reactions_given()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $this->assertEquals(1, $user->totalReactionsGiven());
    }

    public function test_reaction_summary()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $summary = $user->reactionSummary();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $summary);
        $this->assertEquals(1, $summary->get('like'));
    }

    public function test_reacted_items()
    {
        $user = User::factory()->create();
        $article1 = Article::factory()->create();
        $article2 = Article::factory()->create();

        $article1->addReaction('like', $user);
        $article2->addReaction('dislike', $user);

        $items = $user->reactedItems();

        $this->assertInstanceOf(Collection::class, $items);
        $this->assertCount(2, $items);

        $filtered = $user->reactedItems('dislike', Article::class);
        $this->assertTrue($filtered->contains(fn($item) => $item->is($article2)));
    }

    public function test_reactions_to_type()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('love', $user);

        $reactions = $user->reactionsToType(Article::class);

        $this->assertInstanceOf(Collection::class, $reactions);
        $this->assertCount(1, $reactions);
        $this->assertEquals('love', $reactions->first()->reaction);
    }

    public function test_latest_reactions_given()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $article->addReaction('like', $user);

        $latest = $user->latestReactionsGiven();

        $this->assertInstanceOf(Collection::class, $latest);
        $this->assertCount(1, $latest);
        $this->assertEquals('like', $latest->first()->reaction);
    }
}
