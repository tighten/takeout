Networking Todo:

- [x] Put all containers into a `takeout` network
- [x] Test (somehow?) that this network means other Docker containers can be accessed by their full name (e.g. `TO--mailhog--v1.0.1`) in the target Docker-based Laravel apps
- [x] Figure out how to make it so my MySQL Docker container is accessible as `mysql`--alias
- [ ] How do we make it so people with only one container get `mysql` easily, but people with multiple MySQL containers also have something usable
   
   
  
Brainstorm on the naming situation:
- By default, each service aliases itself to just its service name (e.g. mysql)
- Option 1:
    - If I have multiple MySQL instances, the first is named mysql and the later are named mysql80, etc.

- Option 2:
    -  If I spin up a second MySQL instance, it spins down my first, and then names them all as mysql57, mysql80, etc.

- Option 3:
    - We require a nickname for every new container spun up, and that's used for its alias

- Option 4:
    - Number them. `mysql1`, `mysql2` ,
    etc
