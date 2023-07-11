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
                    <form action="{{ route('close-survey',['id'=>$id])}}" method="POST" class="container">
                        @csrf
                        @if(session('success'))
                        <div class="alert alert-success">{{session('success')}}</div>
                        @endif()


                        <br>
                        <div class="mb-3">
                            <label for="practice_name" class="form-label">Practice Name</label>
                            <input type="text" name="practice_name" class="form-control" id="practice_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="assessment_officer" class="form-label">Assessment Officer</label>
                            <input type="text" name="assessment_officer" class="form-control" id="assessment_officer" required>
                        </div>
                        <div class="mb-3">
                            <label for="reporting_officer" class="form-label">Reporting Officer</label>
                            <input type="text" name="reporting_officer" class="form-control" id="reporting_officer" required>
                        </div>
                        <div class="mb-3">
                            <label for="next_review_date" class="form-label">Next Review Date</label>
                            <input type="text" name="next_review_date" class="form-control" id="next_review_date" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="background-color: #0d6efd;">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>