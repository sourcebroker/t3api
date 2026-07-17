CREATE TABLE tx_functionaltest_domain_model_book (
    title varchar(255) DEFAULT '' NOT NULL,
    author int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tx_functionaltest_domain_model_author (
    name varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_functionaltest_domain_model_product (
    title varchar(255) DEFAULT '' NOT NULL,
    category int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tx_functionaltest_domain_model_category (
    title varchar(255) DEFAULT '' NOT NULL
);
