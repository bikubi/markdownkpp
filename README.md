[Markdown](http://daringfireball.net/projects/markdown/) doesn't come with support for tables, so I made this post-processor. It extends the [syntax for lists](http://daringfireball.net/projects/markdown/syntax#list); if there's two consecutive tab characters (or 4+ spaces) in an `ul`'s `li` text content (at lowest level / not in nested child nodes), that `ul` will be transformed into a `table`. E.g.

```markdown
Heading
=======

* First Column		Second Column
* Second row		some value
```

becomes

```html
<h1>Heading</h1>

<table><tbody>
    <tr><td>First Column</td><td>Second Column</td></tr>
    <tr><td>Second row</td><td>some value</td></tr>
</tbody></table>
```

(indentation added for clarity)

Notes
* the syntax tries to follow markdown's idea of "easy-to-read, easy-to-write". Even if unprocessed, the source is readable.
* it reads from STDIN
* to install, put the script in `/usr/local/bin`, or somewhere in your `PATH`, and give it appropriate execution rights
* Tabs are normalized to 4 spaces by markdown, hence the two tabs rule (as they become *at least* 4 spaces)
* indentation shouldn't affect the process, it doesn't spawn a new cell
* TODO: make it safe for `include`ing (shebang gets in the way atm)
* TODO: support for `thead` and `th`
* TODO: support for `caption`
* TODO: handle `colspan` somehow
* TODO: alternative syntax: use empty `li`s as row-delimiter
