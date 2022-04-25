{{-- @php
    $colClass = '';
    if($color) $colClass = ' ' . $color;
@endphp --}}
{{-- <div class="textcontent{{ $colClass }}"> --}}
<div class="textcontent">
    <div class="inner">
        <div>
            <div class="text">
            {!! $text !!}
            @if ($nextSec && $nextSec['type'] == 'order_form' && $nextSec['checked'])
            {{-- @include('snippets.orderForm') --}}
            @include('snippets.eventix')
            @endif
            </div>
        </div>
        {{-- @if (count($media_gallery))
        <div>
            @foreach ($media_gallery as $image)
            <img src="{{ asset($image['img']) }}" alt="{{ $image['alt'] }}">
            @endforeach
        </div>
        @endif --}}
    </div>
</div>