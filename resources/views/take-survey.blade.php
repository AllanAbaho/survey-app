<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            AML Survey
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('store-survey')}}" method="POST" class="container">
                        @csrf

                        @if(session('success'))
                        <div class="alert alert-success">{{session('success')}}</div>
                        @endif()
                        <br>

                        @include('survey::standard', ['survey' => $survey])

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>