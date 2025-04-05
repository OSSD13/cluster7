@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Pending User Approvals</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($pendingUsers->isEmpty())
                        <div class="alert alert-info">
                            No pending user approvals.
                        </div>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Registered At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ ucfirst($user->role) }}</td>
                                        <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 