@extends('layouts.app')

@section('title', 'Minor Cases')
@section('page-title', 'Minor Cases')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Minor Cases</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sprint</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($minorCases as $case)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">{{ $case->sprint }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $case->card }}</td>
                        <td class="px-6 py-4">{{ $case->description ?? '' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $case->member }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $case->points }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button class="text-blue-500 hover:text-blue-700 mr-2 edit-minor-case" data-id="{{ $case->id }}">
                                Edit
                            </button>
                            <button class="text-red-500 hover:text-red-700 delete-minor-case" data-id="{{ $case->id }}">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No minor cases found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Minor Case Modal -->
<div id="minor-case-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 1000;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-md">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="minor-case-modal-title">Edit Minor Case</h3>
            <form id="minor-case-form" class="mt-4">
                @csrf
                <input type="hidden" id="minor-case-id">
                <div class="mb-4">
                    <label for="minor-case-sprint" class="block text-sm font-medium text-gray-700">Sprint</label>
                    <input type="text" id="minor-case-sprint" name="sprint" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                </div>
                <div class="mb-4">
                    <label for="minor-case-card" class="block text-sm font-medium text-gray-700">Card Detail</label>
                    <input type="text" id="minor-case-card" name="card" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                </div>
                <div class="mb-4">
                    <label for="minor-case-description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="minor-case-description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>
                <div class="mb-4">
                    <label for="minor-case-member" class="block text-sm font-medium text-gray-700">Member</label>
                    <input type="text" id="minor-case-member" name="member" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                </div>
                <div class="mb-4">
                    <label for="minor-case-points" class="block text-sm font-medium text-gray-700">Points</label>
                    <input type="number" id="minor-case-points" name="points" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" step="0.5" min="0" required>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancel-minor-case" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const minorCaseModal = document.getElementById('minor-case-modal');
    const minorCaseForm = document.getElementById('minor-case-form');
    const cancelMinorCaseBtn = document.getElementById('cancel-minor-case');

    // Cancel button click handler
    cancelMinorCaseBtn.addEventListener('click', () => {
        minorCaseModal.classList.add('hidden');
    });

    // Form submit handler
    minorCaseForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.getElementById('minor-case-id').value;
        const formData = new FormData(minorCaseForm);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(`/api/minor-cases/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error('Failed to update minor case');
            }

            minorCaseModal.classList.add('hidden');
            window.location.reload(); // Reload the page to show updated data
            alert('Minor case updated successfully');
        } catch (error) {
            console.error('Error updating minor case:', error);
            alert('Error updating minor case: ' + error.message);
        }
    });

    // Edit minor case handler
    document.querySelectorAll('.edit-minor-case').forEach(button => {
        button.addEventListener('click', async (e) => {
            const id = e.target.dataset.id;
            try {
                const response = await fetch(`/api/minor-cases/${id}`);
                if (!response.ok) {
                    throw new Error('Failed to load minor case');
                }
                const minorCase = await response.json();
                
                document.getElementById('minor-case-id').value = minorCase.id;
                document.getElementById('minor-case-sprint').value = minorCase.sprint;
                document.getElementById('minor-case-card').value = minorCase.card;
                document.getElementById('minor-case-description').value = minorCase.description || '';
                document.getElementById('minor-case-member').value = minorCase.member;
                document.getElementById('minor-case-points').value = minorCase.points;
                
                minorCaseModal.classList.remove('hidden');
            } catch (error) {
                console.error('Error loading minor case:', error);
                alert('Error loading minor case: ' + error.message);
            }
        });
    });

    // Delete minor case handler
    document.querySelectorAll('.delete-minor-case').forEach(button => {
        button.addEventListener('click', async (e) => {
            if (confirm('Are you sure you want to delete this minor case?')) {
                const id = e.target.dataset.id;
                try {
                    const response = await fetch(`/api/minor-cases/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to delete minor case');
                    }

                    window.location.reload(); // Reload the page to show updated data
                    alert('Minor case deleted successfully');
                } catch (error) {
                    console.error('Error deleting minor case:', error);
                    alert('Error deleting minor case: ' + error.message);
                }
            }
        });
    });
});
</script>
@endpush
@endsection