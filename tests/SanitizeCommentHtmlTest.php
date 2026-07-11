<?php

use Kirschbaum\Commentions\Actions\SanitizeCommentHtml;
use Tests\Models\Post;
use Tests\Models\User;

test('it strips script tags', function () {
    expect(SanitizeCommentHtml::run('<p>hello</p><script>alert(1)</script>'))
        ->toBe('<p>hello</p>');
});

test('it strips event handler attributes', function () {
    expect(SanitizeCommentHtml::run('<p onclick="evil()">hello</p>'))
        ->toBe('<p>hello</p>');
});

test('it strips img and iframe elements', function () {
    expect(SanitizeCommentHtml::run('<img src=x onerror="alert(1)">'))->toBe('');
    expect(SanitizeCommentHtml::run('<iframe src="https://evil.test"></iframe>'))->toBe('');
});

test('it removes javascript link schemes but keeps the link text', function () {
    $clean = SanitizeCommentHtml::run('<a href="javascript:alert(1)">click</a>');

    expect($clean)
        ->toContain('click')
        ->not->toContain('javascript:');
});

test('it keeps safe http links', function () {
    expect(SanitizeCommentHtml::run('<a href="https://example.test">link</a>'))
        ->toContain('href="https://example.test"');
});

test('it forces rel="noopener noreferrer" on links to prevent reverse tabnabbing', function () {
    expect(SanitizeCommentHtml::run('<a href="https://example.test" target="_blank">link</a>'))
        ->toContain('rel="noopener noreferrer"');
});

test('it keeps the formatting marks the editor can produce', function () {
    $html = '<p><strong>b</strong> <em>i</em> <u>u</u> <s>s</s> <code>c</code></p>'
        . '<ul><li>one</li></ul><blockquote>quote</blockquote><h2>heading</h2>';

    expect(SanitizeCommentHtml::run($html))->toBe($html);
});

test('it allows headings only up to h3, matching the editor', function () {
    $clean = SanitizeCommentHtml::run('<h2>kept</h2><h4>dropped</h4>');

    expect($clean)
        ->toContain('<h2>kept</h2>')
        ->not->toContain('h4');
});

test('it preserves mention spans and their data attributes', function () {
    $mention = '<span class="mention" data-type="mention" data-id="42" data-label="Jane">@Jane</span>';

    $clean = SanitizeCommentHtml::run($mention);

    expect($clean)
        ->toContain('data-type="mention"')
        ->toContain('data-id="42"')
        ->toContain('data-label="Jane"')
        ->toContain('class="mention"');
});

test('saving a comment sanitizes a malicious body', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $comment = $post->comment('<p>hi</p><img src=x onerror="alert(1)"><script>alert(2)</script>', $user);

    expect($comment->body)
        ->toBe('<p>hi</p>')
        ->not->toContain('onerror')
        ->not->toContain('<script');
});

test('saving a comment keeps a mention intact and resolvable', function () {
    $user = User::factory()->create();
    $mentioned = User::factory()->create();
    $post = Post::factory()->create();

    $comment = $post->comment(
        sprintf(
            'Hey <span class="mention" data-type="mention" data-id="%s" data-label="%s">@%s</span>',
            $mentioned->id,
            $mentioned->name,
            $mentioned->name,
        ),
        $user,
    );

    expect($comment->body)
        ->toContain('data-type="mention"')
        ->toContain(sprintf('data-id="%s"', $mentioned->id));

    expect($comment->getMentioned())
        ->toHaveCount(1)
        ->toContain($mentioned);
});
