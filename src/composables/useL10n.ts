import { inject } from 'vue';

export function useL10n() {
    const l10n = inject<Record<string, string>>('l10n', {});

    function t(key: string): string {
        return l10n[key] ?? key;
    }

    function filesize(bytes: number | string): string {
        const b = typeof bytes === 'string' ? parseInt(bytes, 10) : bytes;
        if (isNaN(b)) return '';
        if (b >= 1_000_000_000) return `${(b / 1_000_000_000).toFixed(2)} ${t('GB')}`;
        if (b >= 1_000_000) return `${(b / 1_000_000).toFixed(2)} ${t('MB')}`;
        if (b > 1_000) return `${(b / 1_000).toFixed(2)} ${t('KB')}`;
        return `${b} ${t('bytes')}`;
    }

    return { l10n, t, filesize };
}
