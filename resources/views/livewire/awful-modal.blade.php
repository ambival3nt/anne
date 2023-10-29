
<div class="modal">
    <div class="modal-box">
            <h3 class="font-bold text-lg">
                <slot></slot>
            </h3>
            <p class="py-4">Press ESC key or click the button below to close</p>
            <div class="modal-action">
                <form method="dialog">
                    <!-- if there is a button in form, it will close the modal -->
                    <button class="btn" wire:click="$set">Close</button>
                </form>
            </div>
    </div>
</div>
