<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?= $survey->name ?>
        </h2>
    </x-slot>

    <style>
        button {
            display: none !important;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <a href="{{route('download-pdf', ['id'=>$survey->id])}}" class="btn btn-success">Download</a><br><br>
                    @include('survey::standard', ['survey' => $survey])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>