@php
    $firstName_b = '';
    $lastName_b = '';
    $EmailAddress = '';
    $dieet = '';
    $aantal = '';
    if(old('Emailadres')) $EmailAddress = old('Emailadres');
    if(old('Voornaam')) $firstName_b = old('Voornaam');
    if(old('Achternaam')) $lastName_b = old('Achternaam');
    if(old('Dieet')) $dieet = old('Dieet');
    if(old('Aantal')) $aantal = old('Aantal');
@endphp
<form action="{{ URL('submit-bestellen-form') }}" method="post" id="orderForm">
    @csrf
    <div class="fieldlist">
        <div @error('Voornaam')class="error" data-err-msg="{{ $message }}"@enderror><label for="form-first-name">Voornaam *</label><br /><input type="text" id="form-first-name" name="Voornaam" value="{{ $firstName_b }}"></div>
        <div @error('Achternaam')class="error" data-err-msg="{{ $message }}"@enderror><label for="form-last-name">Achternaam *</label><br /><input type="text" id="form-last-name" name="Achternaam" value="{{ $lastName_b }}"></div>
    </div>
    {{-- <div class="fieldlist"> --}}
        <div @error('Emailadres')class="error" data-err-msg="{{ $message }}"@enderror><label for="form-email">Email Adres *</label><br /><input type="text" id="form-email" name="Emailadres" value="{{ $EmailAddress }}"></div>
    {{-- </div> --}}
    {{-- <div class="fieldlist"> --}}
        <div><label for="form-dieet">Dieetwensen</label><br /><textarea id="form-dieet" name="Dieet" rows="2" cols="10">{{ $dieet }}</textarea></div>
    {{-- </div> --}}
    <div class="fieldlist">
        <div>
        <label for="form-tickets">Kies een tijdslot</label><br />
        <select id="form-tickets" name="Tijdslot">
            @foreach ($data['tickets'] as $ticket)
            <option value="{{ $ticket->id }}">{{ $ticket->name }} uur (Nog {{ $ticket->stock_quantity }} plekken vrij)</option>
            @endforeach
        </select>
        </div>
    </div>
    <div class="fieldlist">
        <div @error('Aantal')class="error" data-err-msg="{{ $message }}"@enderror><label for="form-amount">Aantal deelnemers *</label><br /><input type="text" id="form-amount" name="Aantal" value="{{ $aantal }}"></div>
    </div>
    {{-- {{ $ticketPrice }} --}}
    <p>Ticket Prijs: &euro;{{ str_replace('.', ',', $data['website_options']['wt_ticket_price']) }}</p>
    <div class="orderSubmitBtnHolder"><button type="submit"><span>Aanmelding afronden</span></button><br />U wordt omgeleid naar de betaalpagina</div>
    <p>Betalingsmogelijkheden: iDeal</p>
</form>
@section('before_closing_body_tag')
    @if($errors->any())
    <script>
        const errors = document.querySelectorAll('.error');
        errors.forEach((el) => {
            const err = document.createElement('span');
            err.classList.add('errMsg');
            err.innerHTML = el.dataset.errMsg;
            el.appendChild(err);
        });
        initMsgHolder();
        showMessage('error', 'fas fa-exclamation-triangle', 'Sommige velden konden niet gevalideerd worden, loop het formulier na op fouten. De bestelling kon niet afgerond worden.', 9000, 200);
    </script>
    @endif
@endsection
<script>
    // const errors = document.querySelectorAll('.error');
    // errors.forEach((el) => {
    //     const err = document.createElement('span');
    //     err.classList.add('errMsg');
    //     err.innerHTML = el.dataset.errMsg;
    //     el.appendChild(err);
    // });
</script>