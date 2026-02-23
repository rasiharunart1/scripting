<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Update Password') }}</h6>
        <small
            class="text-muted">{{ __('Ensure your account is using a long, random password to stay secure.') }}</small>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div class="form-group">
                <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
                <input type="password"
                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                    id="update_password_current_password" name="current_password" autocomplete="current-password">
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
                <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                    id="update_password_password" name="password" autocomplete="new-password">
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="update_password_password_confirmation"
                    class="form-label">{{ __('Confirm Password') }}</label>
                <input type="password"
                    class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                    id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password">
                @error('password_confirmation', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-key mr-1"></i>{{ __('Update Password') }}
                </button>

                @if (session('status') === 'password-updated')
                    <span class="ml-3 text-success">
                        <i class="fas fa-check-circle"></i> {{ __('Password updated successfully!') }}
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>
