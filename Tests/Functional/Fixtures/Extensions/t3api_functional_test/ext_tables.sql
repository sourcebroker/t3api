CREATE TABLE tx_functionaltest_domain_model_book (
    title varchar(255) DEFAULT '' NOT NULL,
    author int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tx_functionaltest_domain_model_author (
    name varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_functionaltest_domain_model_product (
    title varchar(255) DEFAULT '' NOT NULL,
    category int(11) unsigned DEFAULT '0' NOT NULL,
    tags int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tx_functionaltest_domain_model_category (
    title varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_functionaltest_domain_model_tag (
    title varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_functionaltest_product_tag_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid_local, uid_foreign),
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_functionaltest_domain_model_article (
    title varchar(255) DEFAULT '' NOT NULL
);
