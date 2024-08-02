<x-two-factor::app>
    <div class="card">
        <div class="card-header">
            {{ __('Two Factor Authentication') }}
        </div>

        <div class="card-body">
            <p>
                {{ __('Enable two factor authentication for you account.') }}
            </p>
            <x-two-factor::session-status :status="session('status')" />

            <form method="POST" action="{{ route('two-factor::activation.store') }}">
                @csrf

                <div class="form-group row">
                    <div class="col-md-6">
                        <x-two-factor::input-error :messages="$errors->get('code')" />
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-8 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Enable') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-two-factor::app>
