<div class="card shadow mb-4 border-left-danger">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-danger">{{ __('Delete Account') }}</h6>
        <small class="text-muted">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </small>
    </div>
    <div class="card-body">
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteAccountModal">
            <i class="fas fa-trash mr-1"></i>{{ __('Delete Account') }}
        </button>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ __('Confirm Account Deletion') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">{{ __('Are you sure you want to delete your account?') }}</h6>
                        <p class="mb-0">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input type="password"
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="password"
                            name="password" placeholder="{{ __('Enter your password to confirm') }}" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>{{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>{{ __('Delete Account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#deleteAccountModal').modal('show');
            });
        </script>
    @endpush
@endif
