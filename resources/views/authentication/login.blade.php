<x-two-factor::app>
    <div class="card">
        <div class="card-header">
            {{ __('Two Factor Authentication') }}
        </div>

        <div class="card-body">
            <p>
                {{ __('Please enter your one-time password to complete your login.') }}
            </p>

            <form method="POST" action="{{ route('two-factor-authentication::store') }}">
                @csrf

                <div class="form-group row">
                    <label for="code" class="col-md-4 col-form-label text-md-right">
                        {{ __('One Time Password') }}
                    </label>

                    <div class="col-md-6">
                        <input id="code"
                               type="text"
                               class="form-control @error('code') is-invalid @enderror"
                               name="code"
                               required
                               autofocus>
                        <x-input-error :messages="$errors->get('code')" />
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Verify') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-two-factor::app>