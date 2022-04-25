@if (count($services))
<div class="servicesSection">
    <div class="inner">
        @foreach ($services as $ser)
        <div class="serviceSection">
            <div><i class="fas fa-{{ $ser['icon'] }}"></i></div>
            <div>
                {!! $ser['text'] !!}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif