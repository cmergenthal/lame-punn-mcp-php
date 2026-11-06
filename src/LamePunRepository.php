<?php

declare(strict_types=1);

namespace LamePunnMcp;

final class LamePunRepository
{
    /**
     * @var array<string, list<string>>
     */
    private array $puns = [
        'classic' => [
            'I tried to make a belt out of watches, but it was a waist of time.',
            'I used to be a baker, but I could not make enough dough.',
            'I wondered why the baseball kept getting bigger, then it hit me.',
            'I bought a boat because it was for sail.',
        ],
        'food' => [
            'I relish the fact that you mustard the strength to ketchup.',
            'The cheese factory exploded. There was de-brie everywhere.',
            'I wanted to tell a joke about herbs, but it was not thyme.',
            'The grape stopped in the middle of the road because it ran out of juice.',
        ],
        'animals' => [
            'A pony could not sing because it was a little hoarse.',
            'The fish got bad grades because it was below sea level.',
            'The cat joined the band because it had perfect purr-cussion.',
            'The frog parked illegally because it got toad.',
        ],
        'work' => [
            'The calendar factory fired me because I took a day off.',
            'I was going to start a paper company, but it folded.',
            'The elevator business has its ups and downs.',
            'I could not become a banker because I lost interest.',
        ],
    ];

    /**
     * @return list<string>
     */
    public function categories(): array
    {
        return array_keys($this->puns);
    }

    public function random(?string $category = null): array
    {
        $resolvedCategory = $category !== null && isset($this->puns[$category])
            ? $category
            : array_rand($this->puns);

        $puns = $this->puns[$resolvedCategory];
        $pun = $puns[array_rand($puns)];

        return [
            'category' => $resolvedCategory,
            'pun' => $pun,
        ];
    }
}
