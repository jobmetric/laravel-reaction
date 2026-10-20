<?php

namespace JobMetric\Like;

use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class LikeServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @throws MigrationFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-like')
            ->hasConfig()
            ->hasTranslation()
            ->hasMigration();
    }
    /**
     * Register committed business outcome definitions alongside legacy events.
     *
     * @return void
     */
    public function afterBootPackage(): void
    {
        if ($this->app->bound('EventRegistry')) {
            $registry = $this->app->make('EventRegistry');
            $registry->register(\JobMetric\Like\Events\Like\LikeStoreCommittedEvent::class);
            $registry->register(\JobMetric\Like\Events\Like\LikeUpdateCommittedEvent::class);
            $registry->register(\JobMetric\Like\Events\Like\LikeDeleteCommittedEvent::class);
        }
    }
}
