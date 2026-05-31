export interface AttachmentFile {
    id: number;
    name: string;
    url: string;
    size: number | string;
    description: string;
}

export interface UploadItem {
    id: number;
    name: string;
    size: number;
    nativeFile: File;
}

export interface SectionOptions {
    $wrapper: JQuery;
    files: AttachmentFile[];
    l10n: Record<string, string>;
    max_upload_size: number;
    product_id: number;
}
