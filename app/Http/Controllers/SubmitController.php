<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubmitController extends Controller
{

    public function submitOrderForm(Request $request) {
        $options = PagesController::getWebsiteOptions();
// dd($options);
        $toValidate = array(
            'Voornaam' => 'required',
            'Achternaam' => 'required',
            'Aantal' => 'required|integer',
            'Emailadres' => 'required|email',
        );
        $validationMessages = array(
            'Voornaam.required' => 'Geef een voornaam op.',
            'Achternaam.required' => 'Geef een achternaam op.',
            'Aantal.required' => 'Geef het aantal deelnemers op.',
            'Aantal.integer' => 'Geef een aantal op.',
            'Emailadres.required' => 'Geef een e-mail adres op.',
            'Emailadres.email' => 'Het e-mail adres is niet juist geformuleerd.',
        );
        /*  Using manually created validator, this line:
            $validated = $request->validate($toValidate,$validationMessages);
            is not redirecting properly when requesting over HTTPS
        */
        // $validated = $request->validate($toValidate,$validationMessages);
        $validator = Validator::make($request->all(), $toValidate, $validationMessages);
        if($validator->fails()) {
            return redirect(route('aanmelden'))
                        ->withErrors($validator)
                        ->withInput();
        }

        $mollieCheckoutIdentifier = (date('U') - 1000000000); // beetje crappy, maar moet maar even.




        $shipping = array();
        $billing = array();
        $items = array();

        $billing['first_name'] = $request->get('Voornaam');
        $billing['last_name'] = $request->get('Achternaam');
        // $billing['address_1'] = $request->get('Factuuradres_StraatEnHuisnummer');
        // $billing['city'] = $request->get('Factuuradres_Postcode');
        // $billing['postcode'] = $request->get('Factuuradres_Woonplaats');
        // if($request->get('toggleDeliveryAddr') == 'Ja') {
        //     $shipping['first_name'] = $request->get('Bezorgadres_Voornaam');
        //     $shipping['last_name'] = $request->get('Bezorgadres_Achternaam');
        //     $shipping['address_1'] = $request->get('Bezorgadres_StraatEnHuisnummer');
        //     $shipping['city'] = $request->get('Bezorgadres_Woonplaats');
        //     $shipping['postcode'] = $request->get('Bezorgadres_Postcode');
        // } else {
            $shipping = $billing;
        // }
        $customerEmail = $request->get('Emailadres');
        // if($loggedInUserId) {
        //     $custApi = new WooGetCustomersApi($loggedInUserId);
        //     $custApi->setHttpBasicAuth();
        //     $customer = $custApi->get();
        //     $customerEmail = $customer->email;
        // }
        $billing['email'] = $customerEmail;
        // if(session_status() != 2) session_start();
        // if(isset($_SESSION['miron_cart'])) {
            // foreach($_SESSION['miron_cart'] as $id => $total) {
                $itemOrdered = array();
                $itemOrdered['product_id'] = 79;
                $itemOrdered['quantity'] = $request->get('Aantal');
                $items[] = $itemOrdered;
            // }
        // }
// dd($shipping, $billing, $items);

        $wooOrder = ShopController::createWooOrder($shipping, $billing, $items, 0);
        $mollieCheckoutIdentifier = $wooOrder->id;




        // $to_email = 'michele@wtmedia-events.nl';
        $to_email = 'leon.kuijf@gmail.com';
        // $to_email = 'frans@tamatta.org, rense@tamatta.org';
        $subject = 'Aanmelding vanaf rotterdamsehorecawandeling.nl (Bestelnummer ' . $mollieCheckoutIdentifier . ')';
        $subjectClient = 'Bevestiging van uw aanmelding op rotterdamsehorecawandeling.nl (Bestelnummer ' . $mollieCheckoutIdentifier . ')';

        $message = 'De volgende informatie is verzonden:
        
            Voornaam: ' . $request->get('Voornaam') . '
            Achternaam: ' . $request->get('Achternaam') . '
            Email adres: ' . $request->get('Emailadres') . '
            Dieet: ' . $request->get('Dieet') . '
            Aantal: ' . $request->get('Aantal') . '

            Bestelnummer: ' . $mollieCheckoutIdentifier . '
            ';
        
        $messageClient = $message;
        $messageClient .= '
Uw aanmelding is definitief wanneer wij de betaling hebben ontvangen.

Met vriendelijke groet,
Rotterdamse Horeca Wandeling -team
        ';

        $headers = array(
            "From: aanmeldformulier@rotterdamsehorecawandeling.nl",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=ISO-8859-1",
            "X-Priority: 1",
        );
        $headers = implode("\r\n", $headers);
        // mail($to_email, $subject, $message, $headers);
        mail($to_email, $subject, $message);
        mail($request->get('Emailadres'), $subjectClient, $messageClient);

        $totalPrice = $request->get('Aantal') * $options['wt_ticket_price'];
        $totalPrice = number_format($totalPrice, 2, '.', '');
        //Go To Mollie checkout
// dd($mollieCheckoutIdentifier, $totalPrice);
        return $this->mollieCheckout($mollieCheckoutIdentifier, $totalPrice);
        // return redirect('/bestelling/' . $mollieCheckoutIdentifier);
    }
    public function mollieCheckout($id, $sum) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.mollie.com/v2/payments',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'amount%5Bcurrency%5D=EUR&amount%5Bvalue%5D=' . $sum . '&description=Bestelnummer%20%23' . $id . '&redirectUrl=https%3A%2F%2Frotterdamsehorecawandeling.nl%2Fbestelling%2F' . $id . '%2F&webhookUrl=https%3A%2F%2Fwebshop.example.org%2Fpayments%2Fwebhook%2F&metadata=%7B%22order_id%22%3A%20%22' . $id . '%22%7D&method=ideal',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer live_jgWMcwsFqgsQbVEh66J6SUafyt8Pyb',
            'Content-Type: application/x-www-form-urlencoded'
          ),
        ));
        
        $response = json_decode(curl_exec($curl));
// dd($response);
        curl_close($curl);
        // echo $response;
        if(isset($response->_links->checkout->href)) {
            $checkoutUrl = $response->_links->checkout->href;
// dd($checkoutUrl);
            return redirect()->away($checkoutUrl);
        } else {
            return false;
        }
    }

    public function submitContactForm(Request $request) {
        $validated = $request->validate([
            'Naam' => 'required',
            'Emailadres' => 'required|email',
            'Bericht' => 'required',
        ],[
            'Naam.required'=> 'Geef a.u.b. een naam op.',
            'Emailadres.required'=> 'Geef a.u.b. een e-mail adres op.',
            'Emailadres.email'=> 'Het e-mail adres is niet juist geformuleerd.',
            'Bericht.required'=> 'Er is geen bericht ingevoerd.',
        ]);

        $to_email = 'leon.kuijf@gmail.com';
        // $to_email = 'frans@tamatta.org, rense@tamatta.org';
        $subject = 'Ingevuld contactformulier vanaf Jusbros.nl';
        $message = 'De volgende informatie is verzonden:
        
            Naam: ' . $request->get('Naam') . '
            Email adres: ' . $request->get('Emailadres') . '
            Bericht: ' . $request->get('Bericht') . '
            Aanmelden nieuwsbrief: ' . ($request->get('AanmeldenNieuwsbrief')?$request->get('AanmeldenNieuwsbrief'):'Nee (niet aangevinkt)') . '
            ';

        $headers = array(
            "From: contactformulier@jusbros.nl",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=ISO-8859-1",
            "X-Priority: 1",
        );
        $headers = implode("\r\n", $headers);
        // mail($to_email, $subject, $message, $headers);
        mail($to_email, $subject, $message);
        return back()->with('success', 'Bedankt dat u contact met ons heeft opgenomen, we zullen uw bericht zo snel mogelijk in behandeling nemen!');
    }
    public function submitBestellenForm(Request $request) {
        $validated = $request->validate([
            'Betreft' => 'required',
            'Bedrijfsnaam' => 'required',
            'Contactpersoon' => 'required',
            'Emailadres' => 'required|email',
            'Bericht' => 'required',
        ],[
            'Betreft.required'=> 'Geef a.u.b. de reden van toenadering aan.',
            'Bedrijfsnaam.required'=> 'Geef a.u.b. een bedrijfsnaam op.',
            'Contactpersoon.required'=> 'Geef a.u.b. een contactpersoon op.',
            'Emailadres.required'=> 'Geef a.u.b. een e-mail adres op.',
            'Emailadres.email'=> 'Het e-mail adres is niet juist geformuleerd.',
            'Bericht.required'=> 'Er is geen bericht ingevoerd.',
        ]);

        $to_email = 'leon.kuijf@gmail.com';
        // $to_email = 'frans@tamatta.org, rense@tamatta.org';
        $subject = 'Ingevuld bestelformulier vanaf Jusbros.nl';
        $message = 'De volgende informatie is verzonden:
        
            Betreft: ' . $request->get('Betreft') . '
            Bedrijfsnaam: ' . $request->get('Bedrijfsnaam') . '
            Contactpersoon: ' . $request->get('Contactpersoon') . '
            Email adres: ' . $request->get('Emailadres') . '
            Bericht: ' . $request->get('Bericht') . '
            ';

        $headers = array(
            "From: bestelformulier@jusbros.nl",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=ISO-8859-1",
            "X-Priority: 1",
        );
        $headers = implode("\r\n", $headers);
        // mail($to_email, $subject, $message, $headers);
        mail($to_email, $subject, $message);
        return back()->with('success', 'Bedankt dat u contact met ons heeft opgenomen, we zullen uw bericht zo snel mogelijk in behandeling nemen!');
    }
}