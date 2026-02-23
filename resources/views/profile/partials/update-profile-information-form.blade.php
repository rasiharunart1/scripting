<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Profile Information') }}</h6>
        <small class="text-muted">{{ __("Update your account's profile information and email address.") }}</small>
    </div>
    <div class="card-body">
        <!-- Email Verification Form (Hidden) -->
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="form-group">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                    name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                    <div class="alert alert-warning mt-2">
                        <small>
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification"
                                class="btn btn-link btn-sm p-0 align-baseline text-decoration-underline">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </small>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2">
                            <small>{{ __('A new verification link has been sent to your email address.') }}</small>
                        </div>
                    @endif
                @endif
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>{{ __('Save Changes') }}
                </button>

                @if (session('status') === 'profile-updated')
                    <span class="ml-3 text-success">
                        <i class="fas fa-check-circle"></i> {{ __('Saved successfully!') }}
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>
