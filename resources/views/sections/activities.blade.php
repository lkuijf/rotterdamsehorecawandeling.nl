@if (count($activities))
<div class="activitiesSection">
    <div class="inner">
        @foreach ($activities as $act)
        <div>
            {!! $act !!}
        </div>
        @endforeach
    </div>
</div>
@endif