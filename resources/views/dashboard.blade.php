<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    You're logged in!<br><br>
                    @if(session('success'))
                    <div class="alert alert-success">{{session('success')}}</div>
                    @endif()

                    <a href="{{route('start-survey')}}" class="btn btn-primary">Start Survey</a><br><br>

                    <h1><b>Recent Surveys</b></h1>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Company Name</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < count($surveys); $i++) : ?>
                                <tr>
                                    <th scope="row"><?= $i + 1; ?></th>
                                    <td><?= $surveys[$i]['name'] ?></td>
                                    <td>
                                        <a class="btn btn-primary" href="{{route('take-survey',['id'=>$surveys[$i]['id']])}}"> Edit</a>
                                        @if (Auth::id() == 1)
                                        <a href="{{route('download-pdf', ['id'=>$surveys[$i]['id']])}}" class="btn btn-success">Download</a>
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>