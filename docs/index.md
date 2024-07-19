# Snapshot Store Documentation

Snapshotting is a technique used to reduce the number of events that need to be replayed to reconstitute an aggregate. Snapshots are taken periodically and stored in a snapshot store. When an aggregate is loaded, the snapshot is loaded first and then the events are replayed on top of the snapshot.
