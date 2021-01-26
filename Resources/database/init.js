let cols = db.getCollectionNames();
if ( cols.length === 0) {
    db.createCollection("feeds");
    db.feeds.createIndex({"nextUpdate": 1, "status": 1});
    db.feeds.createIndex({"url": 1}, {"unique": 1});
    db.feeds.createIndex({"slug": 1}, {"unique": 1});

    db.createCollection("items");
    db.items.createIndex({"lastModified": -1, "feedId": 1});
    db.items.createIndex({"feedId": 1, "publicId": 1}, {"unique": 1});

    db.createCollection("results");
    db.items.createIndex({"eventDate": -1, "feedId": 1});
}
