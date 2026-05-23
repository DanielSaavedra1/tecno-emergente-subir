import { LoaderIcon } from 'lucide-react';

type RunButtonProps = {
    processing?: boolean;
    disabled?: boolean;
    text?: string;
    onClick?: () => void;
};

export function RunButton({
    processing = false,
    disabled = false,
    text = 'COMPILAR',
    onClick,
}: RunButtonProps) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={processing || disabled}
            className="inline-flex items-center gap-2 rounded-md bg-secondary px-6 py-2.5 text-xs font-bold tracking-widest text-white uppercase transition-all hover:bg-primary active:scale-[0.96] disabled:cursor-not-allowed disabled:opacity-40"
        >
            {processing && <LoaderIcon className="size-3.5 animate-spin" />}
            {text}
        </button>
    );
}
