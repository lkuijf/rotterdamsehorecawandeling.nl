@foreach ($data['content_sections'] as $k => $section)
    @php
        // getting next section, so it can be placed within the text-wrapper.
        // not very nice solution but it will do for now.
        $nextSection = false;
        if(isset($data['content_sections'][$k+1])) $nextSection = $data['content_sections'][$k+1];
    @endphp
    @if ($section['type'] == '_anchor')
        <a id="{{ $section['value'] }}" class="anchorPoint"></a>
    @endif
    @if ($section['type'] == 'hero')
        @include('sections.hero', ['image' => $section['img']])
    @endif
    @if ($section['type'] == 'text')
        {{-- @include('sections.text', ['text' => $section['text'], 'color' => $section['color'], 'media_gallery' => $section['gallery']]) --}}
        @include('sections.text', ['text' => $section['text'], 'nextSec' => $nextSection])
    @endif
    @if ($section['type'] == 'solutions')
        @include('sections.solutions', ['solutions' => $section['icon_boxes']])
    @endif
    @if ($section['type'] == 'activities')
        @include('sections.activities', ['activities' => $section['fields']])
    @endif
    @if ($section['type'] == 'services')
        @include('sections.services', ['services' => $section['icon_boxes']])
    @endif
@endforeach