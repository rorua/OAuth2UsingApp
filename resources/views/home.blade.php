@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Dashboard</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if(isset($message))
                            {{ $message }}
                        @else
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="{{ auth()->user()->picture }}" class="rounded img-thumbnail" alt="">
                                </div>
                                <div class="col-md-8">

                                    <table class="table table-hover">
                                        <tbody>
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ auth()->user()->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Surname </th>
                                            <td>{{ auth()->user()->surname }}</td>
                                        </tr>

                                        @if(auth()->user()->birthday)
                                        <tr>
                                            <th>Birthday </th>
                                            <td>{{ auth()->user()->birthday }}</td>
                                        </tr>
                                        @endif

                                        <tr>
                                            <th>email </th>
                                            <td>{{ auth()->user()->email }}</td>
                                        </tr>

                                        <tr>
                                            <th>Auth provider </th>
                                            <td>{{ auth()->user()->provider }}</td>
                                        </tr>

                                        </tbody>
                                    </table>


                                </div>
                            </div>


                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
