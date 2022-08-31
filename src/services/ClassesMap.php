<?php

namespace matfish\EntryMeta\services;

interface ClassesMap
{
  public const LOOK_UP = [
        'entry'=>[
            \craft\records\Entry::class,
            \craft\elements\Entry::class
        ],
        'user'=>[
            \craft\records\User::class,
            \craft\elements\User::class
        ],
        'tag'=>[
            \craft\records\Tag::class,
            \craft\elements\Tag::class
        ],
        'asset'=>[
            \craft\records\Asset::class,
            \craft\elements\Asset::class
        ],
        'category'=>[
            \craft\records\Category::class,
            \craft\elements\Category::class
        ]
    ];

}