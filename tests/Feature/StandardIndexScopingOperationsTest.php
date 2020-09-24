<?php

namespace Orion\Tests\Feature;

use Carbon\Carbon;
use Orion\Tests\Fixtures\App\Models\Post;

class StandardIndexScopingOperationsTest extends TestCase
{
    /** @test */
    public function getting_a_list_of_scoped_resources_without_parameters()
    {
        $matchingPost = factory(Post::class)->create(['publish_at' => Carbon::now()->subHours(3)])->refresh();
        factory(Post::class)->create(['publish_at' => null])->refresh();

        $response = $this->post('/api/posts/search', [
            'scopes' => [
                ['name' => 'published']
            ]
        ]);

        $this->assertResourceListed(
            $response,
            $this->makePaginator([$matchingPost], 'posts/search')
        );
    }

    /** @test */
    public function getting_a_list_of_scoped_resources_with_parameters()
    {
        $matchingPost = factory(Post::class)->create(['publish_at' => Carbon::parse('2019-01-10 09:35:21')])->refresh();
        factory(Post::class)->create(['publish_at' => null])->refresh();

        $response = $this->post('/api/posts/search', [
            'scopes' => [
                ['name' => 'publishedAt', 'parameters' => ['2019-01-10 09:35:21']]
            ]
        ]);

        $this->assertResourceListed(
            $response,
            $this->makePaginator([$matchingPost], 'posts/search')
        );
    }

    /** @test */
    public function getting_a_list_of_scoped_resources_if_scope_is_not_whitelisted()
    {
        factory(Post::class)->times(5)->create();

        $response = $this->post('/api/posts/search', [
            'scopes' => [
                ['name' => 'withMeta']
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['scopes.0.name']]);
    }
}
