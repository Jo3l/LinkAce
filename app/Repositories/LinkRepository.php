<?php

namespace App\Repositories;

use App\Helper\LinkAce;
use App\Helper\LinkIconMapper;
use App\Jobs\SaveLinkToWaybackmachine;
use App\Models\Link;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;
use Tests\Unit\Models\LinkDeleteTest;

class LinkRepository
{
    /**
     * @param array $data
     * @return Link
     */
    public static function create(array $data): Link
    {
        // Try to get the meta information of the URL if no title / description was provided
        $link_meta = LinkAce::getMetaFromURL($data['url']);

        $data['title'] = $data['title'] ?: $link_meta['title'];
        $data['description'] = $data['description'] ?: $link_meta['description'];
        $data['user_id'] = auth()->user()->id;
        $data['icon'] = LinkIconMapper::mapLink($data['url']);
        $data['category_id'] = isset($data['category_id']) && $data['category_id'] > 0 ? $data['category_id'] : null;

        $link = Link::create($data);

        if (isset($data['tags'])) {
            self::updateTagsForLink($link, $data['tags']);
        }

        SaveLinkToWaybackmachine::dispatch($link);

        return $link;
    }

    /**
     * @param Link  $link
     * @param array $data
     * @return Link
     */
    public static function update(Link $link, array $data): Link
    {
        $data['icon'] = LinkIconMapper::mapLink($data['url'] ?? $link->url);

        $link->update($data);

        if (isset($data['tags'])) {
            self::updateTagsForLink($link, $data['tags']);
        }

        return $link;
    }

    /**
     * @param Link $link
     * @return bool
     */
    public static function delete(Link $link): bool
    {
        try {
            $link->delete();
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }

        return true;
    }

    /**
     * @param Link   $link
     * @param array<string> $tags
     */
    protected static function updateTagsForLink(Link $link, array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $new_tags = [];

        foreach ($tags as $tag) {
            $new_tag = Tag::firstOrCreate([
                'user_id' => auth()->user()->id,
                'name' => $tag,
            ]);

            $new_tags[] = $new_tag->id;
        }

        $link->tags()->sync($new_tags);
    }
}
