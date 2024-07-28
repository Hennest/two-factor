<x-two-factor::app>
    <div class="card">
        <div class="card-header">
            {{ __('Two Factor Authentication') }}
        </div>

        <div class="card-body">
            <p>
                {{ __('2FA is currently enabled on your account.') }}
            </p>
            <form method="POST" action="{{ route('two-factor-activation::destroy') }}">
                @csrf
                @method('DELETE')

                <div class="form-group row">
                    <label for="current-password" class="col-md-4 col-form-label text-md-right">
                        {{ __('Current Password') }}
                    </label>

                    <div class="col-md-6">
                        <input id="current-password"
                               type="password"
                               class="form-control @error('current-password') is-invalid @enderror"
                               name="current-password"
                               required
                               autofocus
                               autocomplete="new-password"
                        >
                        <x-input-error :messages="$errors->twoFactorDeletion->get('password')" />
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Disable') }}
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-two-factor::app>