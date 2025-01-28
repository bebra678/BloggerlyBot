
{{--    <h1>Массовая рассылка</h1>--}}

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.message.send') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="message">Сообщение</label>
            <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Отправить</button>
    </form>

