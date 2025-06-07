<?php

namespace App\Providers;

use App\Repositories\NovelRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Volume\VolumeRepository;
use App\Repositories\Chapter\ChapterRepository;
use App\Repositories\Category\CategoryRepository;
use App\Interfaces\Novel\NovelRepositoryInterface;
use App\Interfaces\Volume\VolumeRepositoryInterface;
use App\Interfaces\Chapter\ChapterRepositoryInterface;
use App\Interfaces\Category\CategoryRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register() 
    {
        $this->app->bind(NovelRepositoryInterface::class, NovelRepository::class);
        $this->app->bind(VolumeRepositoryInterface::class, VolumeRepository::class);
        $this->app->bind(ChapterRepositoryInterface::class, ChapterRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
