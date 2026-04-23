<!DOCTYPE html>

<body
    @if (!empty($background)) style="background: url('{{ $background }}') no-repeat center; background-size: cover;" @endif>

    <div class="content" style="position: relative; min-height: 600px; z-index: 1;">
        @if ($is_private_and_secret)
            <p class="is_private_and_secret">
                سري و خاص
            </p>
        @endif
        {!! $content !!}
    </div>
    @if (!empty($signature_image))
        <div
            style="position: absolute; bottom: 10%; left: 10%; margin-top: 20px !important; margin-left: 30px !important; z-index: 2;">
            <img src="{{ $signature_image }}" style="max-width: 150px; opacity: 0.8;" />
        </div>
    @endif
</body>

</html>
