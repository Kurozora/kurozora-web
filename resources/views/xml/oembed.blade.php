<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<oembed>
    <type>{{ $type }}</type>
    <version>{{ $version }}</version>
    <cache_age>{{ $cache_age }}</cache_age>
    <provider_name>{{ $provider_name }}</provider_name>
    <provider_url>{{ $provider_url }}</provider_url>
    <title>{!! $title !!}</title>
    @if (!empty($author_name))
        <author_name>{{ $author_name }}</author_name>
    @endif
    @if (!empty($author_url))
        <author_url>{{ $author_url }}</author_url>
    @endif
    @if (!empty($thumbnail_url))
        <thumbnail_url>{{ $thumbnail_url }}</thumbnail_url>
    @endif
    @if (!empty($thumbnail_width))
        <thumbnail_width>{{ $thumbnail_width }}</thumbnail_width>
    @endif
    @if (!empty($thumbnail_height))
        <thumbnail_height>{{ $thumbnail_height }}</thumbnail_height>
    @endif
    <width>{{ $width }}</width>
    <height>{{ $height }}</height>
    <html>{{ $html }}</html>
</oembed>
