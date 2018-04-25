### Codeigniter AdminLTE + crud generator

##### How to use
- run `composer install`
- run `bower install`
- run `propel init` and follow Propel initialization step


**important: make sure your schema.xml is at your root directory**
###### Create a CRUD with a single command
Open your schema.xml, You can create CRUD controller and views using this command
`php Scaffold generate [TableObject] [fk1].[RelatedTable1]-[fk2].[RelatedTable2]`
** Note: [TableObject] and [RelatedTable] here is the **phpName** attribute from your schema.xml table, while [fk] was **name** attribute

###### Example
In case i have following schema.xml
```
....
<table name="book" idMethod="native" phpName="Books">
    <column name="id" phpName="Id" type="INTEGER" primaryKey="true" autoIncrement="true" required="true"/>
        <column name="title" phpName="Title" type="VARCHAR" />
	    <column name="author_id" phpName="AuthorId" type="INTEGER" />
	        <column name="publisher_id" phpName="PublisherId" type="INTEGER" />
		    <foreign-key foreignTable="author" name="FK_book_author">
		          <reference local="author_id" foreign="id"/>
			      </foreign-key>
			          <foreign-key foreignTable="publisher" name="FK_book_publisher">
				        <reference local="publisher_id" foreign="id"/>
					    </foreign-key>
					        <vendor type="mysql">
						      <parameter name="Engine" value="InnoDB"/>
						          </vendor>
							    </table>
							      <table name="author" phpName="Author">
							           <column name="id" phpName="Id" type="INTEGER" primaryKey="true" autoIncrement="true" required="true"/>
								       <column name="name">
								         </table>
									   ....
									     ```
									     You can generate CRUD book by using this command:

									     `php Scaffold generate Book author_id.Author-publisher_id.Publisher`


## Requirements ##

+ composer
+ run composer install
+ run ./propel diff
+ run ./propel migrate
+ add * * * * * php /path/to/project/index.php cli/Task run to crontab
