@extends('templates.rdamhorecawandeling')
@section('content')
<div id="contentWrapper" class="checkoutWrapper">
    <div class="textcontent">
        <div class="inner">
            <div>
                <div class="text">
                    {!! $data['website_options']['wt_checkout_ok'] !!}
                    <p><a href="/">< terug naar de website</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection