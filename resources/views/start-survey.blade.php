<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Start Survey
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('submit-survey')}}" method="POST" class="container">
                        @csrf

                        <br>
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Select Type</label>
                            <select class="form-select" aria-label="Default select example" name="type">
                                <option value="firm">Firm</option>
                                <option value="company">Company</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Company / Firm Name</label>
                            <input type="text" class="form-control" name="name" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background-color: #0d6efd;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>