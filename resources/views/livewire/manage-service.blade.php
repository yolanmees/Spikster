<div>
    @if($message)
    <div class="alert alert-info">{{ $message }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Service</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
            <tr>
                <td>{{ $service['name'] }}</td>
                <td>{{ $service['status'] }}</td>
                <td>
                    <button wire:click="restartService('{{ $service['name'] }}')"
                        class="btn btn-primary">Restart</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
