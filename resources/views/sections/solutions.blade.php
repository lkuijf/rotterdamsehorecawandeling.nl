@if (count($solutions))
<div class="iconboxesBanner">
    <div class="inner">
        @foreach ($solutions as $sol)
        <div class="bannerSection">
            <div><i class="fas fa-{{ $sol['icon'] }}"></i></div>
            <div>
                {!! $sol['text'] !!}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif