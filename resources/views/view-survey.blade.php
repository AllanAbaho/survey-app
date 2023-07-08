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
        .form-control:disabled, .form-control[readonly] {
    background-color: transparent;
}
[type='text'],[type='email'],[type='url'],[type='password'],[type='number'],[type='date'],[type='datetime-local'],[type='month'],[type='search'],[type='tel'],[type='time'],[type='week'],[multiple],textarea,select {
  
  border-color: transparent;
  border-width: 0px;
  padding-left: 0rem;
}
.form-control {
    border: 0px;
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