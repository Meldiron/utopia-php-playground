**Setup:**

```
$ docker run --rm --interactive --tty --volume $(pwd):/app composer update --ignore-platform-reqs --optimize-autoloader --no-plugins --no-scripts --prefer-dist
```

**Run app:**

```
$ docker-compose up
```

**Use app:**

```
GET http://localhost:8005/config-keys
```

**Expected behavour:** With every refresh, the array should get longer

**Actual behavour:** With every refresh, we get new instance of the store to array length is always 1

**Problem:** setResource is executed with each request again and again

**"Bad" solution:** Use Utopia registry. That can fix the problem.. But why is there a probleim in the first place? 🤔 Thats the question!
