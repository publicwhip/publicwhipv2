These are the services you should use to interact with the entities. These handle the 'business logic'/validation
and similar services.

These should be the only parts of the code that interact with the database in any manner and should just
accept/return Factories or scalars.