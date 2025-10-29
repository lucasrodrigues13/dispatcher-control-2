{{-- Modal Block User --}}
<div class="modal fade" id="blockUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-ban me-2"></i>
                    Block User Subscription
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to block this user's subscription?</p>
                <div class="mb-3">
                    <label for="blockReason" class="form-label">Reason (Optional):</label>
                    <textarea class="form-control" id="blockReason" rows="3" placeholder="Enter reason for blocking..."></textarea>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    The user will lose access to the system immediately.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmBlockBtn">
                    <i class="fas fa-ban me-2"></i>
                    Block User
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Change Plan --}}
<div class="modal fade" id="changePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Change User Plan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="newPlanSelect" class="form-label">Select New Plan:</label>
                    <select class="form-select" id="newPlanSelect" required>
                        <option value="">Choose a plan...</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">
                                {{ $plan->name }} - ${{ $plan->price }}/month
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="extendsCurrent">
                    <label class="form-check-label" for="extendsCurrent">
                        Extend current subscription period
                    </label>
                    <small class="form-text text-muted">
                        If checked, the new plan will start after the current period ends.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmChangePlanBtn">
                    <i class="fas fa-save me-2"></i>
                    Change Plan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Delete User --}}
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i>
                    Delete User Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to permanently delete this user account?</p>
                <div class="mb-3">
                    <label for="deleteReason" class="form-label">Reason for deletion:</label>
                    <textarea class="form-control" id="deleteReason" rows="3" placeholder="Enter reason for deletion..." required></textarea>
                </div>
                <div class="alert alert-info">
                    <strong>What will happen:</strong>
                    <ul class="mb-0 mt-2">
                        <li>User account will be permanently deleted</li>
                        <li>Subscription will be cancelled</li>
                        <li>All user data will be removed</li>
                        <li>This action will be logged for audit purposes</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>
                    Delete User
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Extend Subscription --}}
<div class="modal fade" id="extendSubscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Extend Subscription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Extend this user's subscription by:</p>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="extendPeriod" class="form-label">Period:</label>
                        <select class="form-select" id="extendPeriod" required>
                            <option value="7">7 Days</option>
                            <option value="30" selected>1 Month</option>
                            <option value="90">3 Months</option>
                            <option value="365">1 Year</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="extendPeriodType" class="form-label">Type:</label>
                        <select class="form-select" id="extendPeriodType" required>
                            <option value="days">Days</option>
                            <option value="months" selected>Months</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmExtendBtn">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Extend Subscription
                </button>
            </div>
        </div>
    </div>
</div>
