<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for creating the 'reaction' table.
 *
 * This table stores polymorphic reactions such as like, dislike, love, angry, sad, etc.,
 * and supports tracking the source, IP, device ID, and soft deletes.
 *
 * Key Features:
 * - Polymorphic relationship for both liker (who reacts) and likeable (what is reacted to)
 * - Reaction type support with default as 'like'
 * - IP and device tracking
 * - Optional source identifier (e.g., 'web', 'mobile')
 * - Unique constraint to prevent duplicate likes by the same entity
 * - Indexed for optimized querying by reaction type
 * - Soft deletes support
 */
return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('reaction.tables.reaction'), function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('reactor');
            // Entity who made the reaction (e.g., User, Admin)

            $table->morphs('reactable');
            // Entity being reacted to (e.g., Post, Comment)

            $table->string('reaction')->default('like')->index();
            // Type of reaction
            // Examples: like, dislike, love, angry, sad

            $table->ipAddress('ip')->nullable()->index();
            // IP address of the reactor (optional)

            $table->string('device_id')->nullable()->index();
            // Device identifier for further tracking (optional)

            $table->string('source')->nullable();
            // Source platform of the reactor (e.g., 'web', 'mobile')

            $table->softDeletes();
            $table->timestamps();

            $table->unique([
                'reactor_type',
                'reactor_id',
                'reactable_type',
                'reactable_id'
            ], 'REACTOR_REACTABLE_UNIQUE');
            // Ensure a reactor can only react once to a specific reactable

            $table->index([
                'reactable_type',
                'reactable_id',
                'reaction'
            ], 'REACTABLE_REACTION_INDEX');
            // Optimization index for filtering by reaction type
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('reaction.tables.reaction'));
    }
};
