<x-two-factor::app>
    <div class="card">
        <div class="card-header">
            {{ __('Two Factor Authentication') }}
        </div>

        <div class="card-body">
            <p>
                {{ __('Scan this QR code with your Google Authenticator App.') }}
            </p>
            <div>
                {{ $qrCode }}
            </div>

            <p>
                {{ __('Alternatively, you can use the code: :secretKey', ['secretKey' => $secretKey]) }}
            </p>

            <form method="POST" action="{{ route('two-factor-confirmation::store') }}">
                @csrf

                <div class="form-group row">
                    <label for="code" class="col-md-4 col-form-label text-md-right">
                        {{ __('Authentication code') }}
                    </label>

                    <div class="col-md-6">
                        <input id="code"
                               type="text"
                               class="form-control @error('code') is-invalid @enderror"
                               name="code"
                               required
                               autofocus>

                        <x-input-error :messages="$errors->confirmTwoFactorAuthentication->get('code')" />
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Confirm') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-two-factor::app>