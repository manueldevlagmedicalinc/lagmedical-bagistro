<?php

namespace LagMedical\Eyewear\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LagMedical\Eblast\Jobs\SyncEblastContact;
use Webkul\Core\Repositories\SubscribersListRepository;

class FramesController
{
    public function __construct(protected SubscribersListRepository $subscriptionRepository) {}

    public function index(): View
    {
        return view('eyewear::campaign.frames.index');
    }

    public function subscribe(): RedirectResponse
    {
        $validated = request()->validate([
            'email' => ['required', 'email', 'max:254'],
            'consent' => ['accepted'],
            'email_address_check' => ['nullable', 'max:0'],
        ]);

        $email = mb_strtolower($validated['email']);
        $channel = core()->getCurrentChannel();
        $existing = $this->subscriptionRepository->findOneByField('email', $email);

        $subscription = $existing
            ? tap($existing)->update(['is_subscribed' => 1, 'channel_id' => $channel->id])
            : $this->subscriptionRepository->create([
                'email' => $email,
                'is_subscribed' => 1,
                'token' => uniqid(),
                'channel_id' => $channel->id,
                'customer_id' => auth()->id(),
            ]);

        DB::table('lagmedical_eblast_consents')->insert([
            'subscriber_id' => $subscription->id,
            'email' => $email,
            'channel_id' => $channel->id,
            'source' => 'frames-campaign-landing',
            'consent_text' => 'Acepto recibir comunicaciones comerciales de LAG Medical por email.',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'consented_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $listKey = core()->getConfigData(
            'emails.configure.eblast.frames_campaign_list_key',
            $channel->code
        ) ?: '9';

        SyncEblastContact::dispatch($email, (int) $channel->id, $listKey);

        return to_route('campaign.frames.index')->with('success', 'Gracias. Tu solicitud fue registrada correctamente.');
    }
}
