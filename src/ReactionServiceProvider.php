<?php

namespace JobMetric\Reaction;

use Illuminate\Contracts\Container\BindingResolutionException;
use JobMetric\EventSystem\Support\EventRegistry;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class ReactionServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @throws MigrationFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-reaction')
            ->hasConfig()
            ->hasTranslation()
            ->hasMigration();
    }

    /**
     * after boot package
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function afterBootPackage(): void
    {
        // Register events if EventRegistry is available
        // This ensures EventRegistry is available if EventSystemServiceProvider is loaded
        if ($this->app->bound('EventRegistry')) {
            /** @var EventRegistry $registry */
            $registry = $this->app->make('EventRegistry');

            // Reaction Events
            $registry->register(\JobMetric\Reaction\Events\ReactionAddEvent::class);
            $registry->register(\JobMetric\Reaction\Events\ReactionRemovedEvent::class);
            $registry->register(\JobMetric\Reaction\Events\ReactionRemovingEvent::class);
        }
    }
}
