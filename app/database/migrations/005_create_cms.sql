-- Content management: all site copy lives in the database (no static pages).
-- content_blocks store WYSIWYG HTML keyed by page + block, so the admin can
-- edit any HTML field. images tracks uploaded media for reuse in the CMS.

CREATE TABLE IF NOT EXISTS content_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(120) NOT NULL,           -- e.g. 'home', 'jazz', 'about'
    block_key VARCHAR(120) NOT NULL,           -- e.g. 'hero', 'intro', 'footer_note'
    html MEDIUMTEXT NULL,                       -- WYSIWYG-edited HTML
    image_path VARCHAR(255) NULL,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_page_block (page_slug, block_key),
    CONSTRAINT fk_cb_user FOREIGN KEY (updated_by) REFERENCES users(UserId) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    path VARCHAR(255) NOT NULL,
    alt VARCHAR(255) NULL,
    uploaded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_img_user FOREIGN KEY (uploaded_by) REFERENCES users(UserId) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
