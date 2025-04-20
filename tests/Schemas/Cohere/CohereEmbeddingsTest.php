<?php

declare(strict_types=1);

namespace Tests\Schemas\Cohere;

use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Embedding;
use Tests\Fixtures\FixtureResponse;

it('can generate embeddings from an input', function (): void {
    FixtureResponse::fakeResponseSequence('invoke', 'cohere/generate-embeddings-from-input', [
        'X-Amzn-Bedrock-Input-Token-Count' => 4,
    ]);

    $response = Prism::embeddings()
        ->using('bedrock', 'cohere.embed-english-v3')
        ->fromInput('Hello, world!')
        ->asEmbeddings();

    $embeddings = json_decode(file_get_contents('tests/Fixtures/cohere/generate-embeddings-from-input-1.json'), true);
    $embeddings = array_map(fn (array $item): Embedding => Embedding::fromArray($item), data_get($embeddings, 'embeddings'));

    expect($response->embeddings)->toBeArray();
    expect($response->embeddings[0]->embedding)->toEqual($embeddings[0]->embedding);
    expect($response->usage->tokens)->toBe(4);
});

it('can generate embeddings from a file', function (): void {
    FixtureResponse::fakeResponseSequence('invoke', 'cohere/generate-embeddings-from-file', [
        'X-Amzn-Bedrock-Input-Token-Count' => 1,
    ]);

    $response = Prism::embeddings()
        ->using('bedrock', 'cohere.embed-english-v3')
        ->fromFile('tests/Fixtures/document.md')
        ->asEmbeddings();

    $embeddings = json_decode(file_get_contents('tests/Fixtures/cohere/generate-embeddings-from-file-1.json'), true);
    $embeddings = array_map(fn (array $item): Embedding => Embedding::fromArray($item), data_get($embeddings, 'embeddings'));

    expect($response->embeddings)->toBeArray();
    expect($response->embeddings[0]->embedding)->toEqual($embeddings[0]->embedding);
    expect($response->usage->tokens)->toBe(1);
});

it('returns multiple embeddings from input', function (): void {
    FixtureResponse::fakeResponseSequence('*', 'cohere/embeddings-from-multiple-inputs', [
        'X-Amzn-Bedrock-Input-Token-Count' => 1,
    ]);

    $response = Prism::embeddings()
        ->using('bedrock', 'cohere.embed-english-v3')
        ->fromInput('The food was delicious.')
        ->fromInput('The drinks were not so good.')
        ->asEmbeddings();

    $embeddings = json_decode(file_get_contents('tests/Fixtures/cohere/embeddings-from-multiple-inputs-1.json'), true);
    $embeddings = array_map(fn (array $item): Embedding => Embedding::fromArray($item), data_get($embeddings, 'embeddings'));

    expect($response->embeddings)->toBeArray();
    expect($response->embeddings[0]->embedding)->toEqual($embeddings[0]->embedding);
    expect($response->embeddings[1]->embedding)->toEqual($embeddings[1]->embedding);
    expect($response->usage->tokens)->toBe(1);
});
