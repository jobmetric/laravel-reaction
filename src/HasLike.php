<?php

namespace JobMetric\Like;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use JobMetric\Like\Enums\LikeTypeEnum;
use JobMetric\Like\Events\LikeForgetEvent;
use JobMetric\Like\Events\LikeStoredEvent;
use JobMetric\Like\Events\LikeUpdateEvent;
use JobMetric\Like\Models\Like;
use Throwable;

/**
 * @method morphOne(string $class, string $string)
 * @method morphMany(string $class, string $string)
 */
trait HasLike
{
    /**
     * like has one relationship
     *
     * @return MorphOne
     */
    public function likeOne(): MorphOne
    {
        return $this->morphOne(Like::class, 'likeable');
    }

    /**
     * like has many relationships
     *
     * @return MorphMany
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * scope locale for select like relationship
     *
     * @return MorphOne
     */
    public function likeTo(): MorphOne
    {
        return $this->likeOne()->where('type', true);
    }

    /**
     * scope locale for select disLike relationship
     *
     * @return MorphOne
     */
    public function dislikeTo(): MorphOne
    {
        return $this->likeOne()->where('type', false);
    }

    /**
     * scope locale for select likes relationship
     *
     * @return MorphMany
     */
    public function likesTo(): MorphMany
    {
        return $this->likes()->where('type', true);
    }

    /**
     * scope locale for select disLikes relationship
     *
     * @return MorphMany
     */
    public function dislikesTo(): MorphMany
    {
        return $this->likes()->where('type', false);
    }

    /**
     * store like and dislike
     *
     * @param int $user_id
     * @param bool $type true: like, false: dislike
     *
     * @return array
     * @throws Throwable
     */
    public function likeIt(int $user_id, bool $type = true): array
    {
        /* @var Like $userLike */
        $userLike = $this->likeOne()->where('user_id', $user_id)->first();

        if ($userLike) {
            if ($userLike->type == $type) {
                $typeData = 'forget';

                $this->likeOne()->where('user_id', $user_id)->delete();

                event(new LikeForgetEvent($userLike));
        if (! $userLike->newQuery()->where($userLike->only(['likeable_type', 'likeable_id', 'user_id']))->exists()) {
            app(\JobMetric\EventSystem\Support\CommittedEventDispatcher::class)->dispatch(new \JobMetric\Like\Events\Like\LikeDeleteCommittedEvent($userLike->likeable_type, (int) $userLike->likeable_id, (int) $userLike->user_id), $userLike->getConnectionName());
        }
            } else {
                $typeData = 'update';

                $this->likeOne()->where('user_id', $user_id)->update(['type' => $type]);

                event(new LikeUpdateEvent($userLike, $type));
        if (true) {
            app(\JobMetric\EventSystem\Support\CommittedEventDispatcher::class)->dispatch(new \JobMetric\Like\Events\Like\LikeUpdateCommittedEvent($userLike->likeable_type, (int) $userLike->likeable_id, (int) $userLike->user_id), $userLike->getConnectionName());
        }
            }
        } else {
            $typeData = 'store';

            $like = $this->likeOne()->create([
                'user_id' => $user_id,
                'type' => $type
            ]);

            event(new LikeStoredEvent($like));
        if ($like->exists) {
            app(\JobMetric\EventSystem\Support\CommittedEventDispatcher::class)->dispatch(new \JobMetric\Like\Events\Like\LikeStoreCommittedEvent($like->likeable_type, (int) $like->likeable_id, (int) $like->user_id), $like->getConnectionName());
        }
        }

        return [
            'type' => $typeData,
            'like' => $this->likeCount(),
            'dislike' => $this->dislikeCount()
        ];
    }

    /**
     * like count
     *
     * @return int
     * @throws Throwable
     */
    public function likeCount(): int
    {
        return $this->likeTo()->count();
    }

    /**
     * dislike count
     *
     * @return int
     * @throws Throwable
     */
    public function dislikeCount(): int
    {
        return $this->dislikeTo()->count();
    }

    /**
     * load like or disLike count after a model loaded
     *
     * @return static
     */
    public function loadLikeDislikeCount(): static
    {
        $this->loadCount(['likeTo as like_count', 'dislikeTo as dislike_count']);

        return $this;
    }

    /**
     * load like or disLike after model loaded
     *
     * @param bool $type true: like, false: dislike
     *
     * @return static
     */
    public function loadLikeDislike(bool $type = true): static
    {
        if ($type) {
            $this->load('likeTo');
        } else {
            $this->load('dislikeTo');
        }

        return $this;
    }

    /**
     * load likes or dislikes after model loaded
     *
     * @param bool $type true: like, false: dislike
     *
     * @return static
     */
    public function loadLikesDislikes(bool $type = true): static
    {
        if ($type) {
            $this->load('likesTo');
        } else {
            $this->load('dislikesTo');
        }

        return $this;
    }

    /**
     * is liked or disliked by user
     *
     * @param int $user_id
     *
     * @return LikeTypeEnum|null like, dislike, null
     */
    public function isLikedDislikedBy(int $user_id): ?LikeTypeEnum
    {
        /* @var Like $like */
        $like = $this->likeOne()->where('user_id', $user_id)->first();

        return $like ? ($like->type ? LikeTypeEnum::LIKE : LikeTypeEnum::DISLIKE) : null;
    }

    /**
     * forget like or dislike
     *
     * @param int $user_id
     * @param bool $type true: like, false: dislike
     *
     * @return static
     */
    public function forgetLike(int $user_id, bool $type = true): static
    {
        /* @var Like $like */
        $like = $this->likeOne()->where('type', $type)->where('user_id', $user_id)->first();

        if ($like) {
            $like->delete();

            event(new LikeForgetEvent($like));
        if (! $like->newQuery()->where($like->only(['likeable_type', 'likeable_id', 'user_id']))->exists()) {
            app(\JobMetric\EventSystem\Support\CommittedEventDispatcher::class)->dispatch(new \JobMetric\Like\Events\Like\LikeDeleteCommittedEvent($like->likeable_type, (int) $like->likeable_id, (int) $like->user_id), $like->getConnectionName());
        }
        }

        return $this;
    }

    /**
     * forget likes or dislikes
     *
     * @param bool $type true: like, false: dislike
     *
     * @return static
     */
    public function forgetLikes(bool $type = true): static
    {
        /* @var Like $like */
        $this->likes()->where('type', $type)->get()->each(function ($like) {
            $like->delete();

            event(new LikeForgetEvent($like));
        if (! $like->newQuery()->where($like->only(['likeable_type', 'likeable_id', 'user_id']))->exists()) {
            app(\JobMetric\EventSystem\Support\CommittedEventDispatcher::class)->dispatch(new \JobMetric\Like\Events\Like\LikeDeleteCommittedEvent($like->likeable_type, (int) $like->likeable_id, (int) $like->user_id), $like->getConnectionName());
        }
        });

        return $this;
    }
}
