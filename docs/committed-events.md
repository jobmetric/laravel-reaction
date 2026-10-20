# Committed business outcomes

Classes are registered by the package provider alongside legacy events. Keys ending in `.committed` are safe automation outcomes; earlier event signatures, payloads and timing are preserved. Only scalar identifiers are exposed.

| Event class | Key | Payload properties |
| --- | --- | --- |
| `JobMetric\Like\Events\Like\LikeStoreCommittedEvent` | `like.like.stored.committed` | `ownerType: string`, `ownerId: int`, `userId: int` |
| `JobMetric\Like\Events\Like\LikeUpdateCommittedEvent` | `like.like.updated.committed` | `ownerType: string`, `ownerId: int`, `userId: int` |
| `JobMetric\Like\Events\Like\LikeDeleteCommittedEvent` | `like.like.deleted.committed` | `ownerType: string`, `ownerId: int`, `userId: int` |

Schedule from the owning operation after successful persistence. Delivery waits for the owning connection outer commit and is discarded on rollback. No-op operations emit nothing. Native Laravel listeners are isolated individually; failures are logged with event key and exception class only. No models, raw inputs, credentials or translated content are included. This is synchronous delivery without durable outbox guarantees.
