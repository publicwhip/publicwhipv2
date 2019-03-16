Providers are (usually) dependency injected objects which provider a service - such as a database, debugger or
similar.

They are called Providers to help distinguish them from 'domain specific' services which handle the
'business logic' which are kept in the 'Services' folder.

Providers only tend to return the same thing to operator on - if you need 'new' things, look at factories.