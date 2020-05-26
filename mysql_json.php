<?php

/*
	添加字符串，结果：["json_array"]
*/
INSERT INTO json (test) VALUES (JSON_ARRAY('json_array'))
/*
	添加对象,结果： {"key": "hello"}  
*/
INSERT Into json (test) VALUES (JSON_OBJECT('KEY','hello'))
/*
	添加合并对象和数组，结果： [{"key": "hello"}, 1, 2]  
*/
INSERT INTO json (test) VALUES (JSON_MERGE_PRESERVE(JSON_OBJECT('key','abc'), JSON_ARRAY('anc')))
/*
	合并数组，JSON_MERGE_PATCH 只保留最后数组参数，结果：[true, false]
*/
SELECT JSON_MERGE_PATCH('[1,2]','["a","b"]','[1,2]','[true,false]')
/*
	合并数组：JSON_MERGE_PRESERVE 按照顺序将数组参数连接起来，结果：[1, 2, "a", "b", "c", 1, 2, true, false]
*/
SELECT JSON_MERGE_PRESERVE('[1,2]','["a","b","c"]','[1,2]','[true,false]')

/*
	合并对象,JSON_MERGE_PATCH只保留最后的传入键值,结果：{"a": 1, "b": 2}
*/
SELECT JSON_MERGE_PATCH('{"a":3,"b":1}', '{"a":1,"b":2}')
/*
	合并对象,JSON_MERGE_PRESERVE重复键的所有值保留为数组,结果：{"a": [1, 2], "b": [1, 2]}
*/
SELECT JSON_MERGE_PRESERVE('{"a":1,"b":1}', '{"a":2,"b":2}')

/*
.keyName：JSON对象中键名为keyName的值；
对于不合法的键名（如有空格），在路径引用中必须用双引号"将键名括起来，例，."key name"；
[index]：JSON数组中索引为index的值，JSON数组的索引同样从0开始；
[index1 to index2]：JSON数组中从index1到index2的值的集合；
.*: JSON对象中的所有value；
[*]: JSON数组中的所有值；
prefix**suffix: 以prefix开头并以suffix结尾的路径；
**.keyName为多个路径，如对于JSON对象'{"a": {"b": 1}, "c": {"b": 2}}','$**.b'指路径$.a.b和$.c.b；
不存在的路径返回结果为NULL；
前导$字符表示当前正在使用的JSON文档
例子：对于数组[3, {"a": [5, 6], "b": 10}, [99, 100]]
$[1]为{"a": [5, 6], "b": 10}。
[1].a为[5, 6]。
$[1].a[1]为 6。
$[1].b为 10。
$[2][0]为 99
*/

/*
	JSON_EXTRACT,结果："jim"
*/
SELECT JSON_EXTRACT('{"id":29,"name":"jim"}','$.name')
/*
	JSON_EXTRACT,结果：[30, "jim"]
*/
SELECT JSON_EXTRACT('{"id":30,"name":"jim"}', '$.*')

/*
	json数组,结果："b"，其中$[1]，表示json数组里面下标是1的内容，下标从0开始
*/
SELECT JSON_EXTRACT('["a","b","C"]', '$[1]')

/*
	json数组,结果："b"，其中$[1]，表示json数组里面下标是1到2的内容，下标从0开始
*/
SELECT JSON_EXTRACT('["a", "b", "c"]', '$[1 to 2]')
/*
	json数组,结果："b"，其中$[*]，表示json数组里面下标所有的内容
*/
SELECT JSON_EXTRACT('["a", "b", "c"]', '$[*]');

/*
JSON_REPLACE 替换值（只替换已经存在的旧值）
JSON_SET 设置值（替换旧值，并插入不存在的新值）
JSON_INSERT 插入值（插入新值，但不替换已经存在的旧值）
JSON_REMOVE 删除JSON数据，删除指定值后的JSON文档
*/

/*结果 ：{"id": 29, "name": "jim2"}*/
SELECT JSON_REPLACE('{"id":29,"name":"jim"}', '$.name',"jim2")

/*结果：{"id": 29, "cat": "mimi", "name": "jim"}*/
SELECT JSON_SET('{"id":29,"name":"jim"}', '$.cat', 'mimi')

/* 结果：[1, 2, 3, 4]*/
SELECT JSON_INSERT('[1,2,3]', '$[3]', 4)






/* 实际开发案例 */


/*查询名字叫李四的数据*/
SELECT * FROM json_tab WHERE json_Info -> '$.name' = '李四'

/*
	查询姓名和他们的年龄,保留双引号
*/
select json_info -> '$.name' as name , json_info -> '$.age' as age FROM json_tab
select JSON_EXTRACT(json_info, '$.name') as name,JSON_EXTRACT(json_info, '$.age') as age FROM json_tab

/*查询姓名和他们的年龄，去掉双引号*/
SELECT json_info->> '$.name' as name,json_info->'$.age' as age FROM json_tab
SELECT JSON_UNQUOTE(JSON_EXTRACT(json_info, '$.name')) as name , JSON_EXTRACT(json_info, '$.age') as age from json_tab

/*查询姓名和他们的姐姐*/
select json_info ->> '$.name' as name,json_info->> '$.sister[*].name' as sisters from json_tab

/*查询姓名和他们的姐姐以及朋友*/
SELECT json_info ->> '$.name' as name,json_info ->> '$.sister[*].name' as sisters,json_info->'$**.friend[*].name' as f from json_tab

/*查询key*/
select JSON_KEYS(json_info) FROM json_tab;
/*查询姐姐的key*/
SELECT JSON_KEYS(json_info,'$.sister[1]') FROM json_tab;

/*查询名字是否存在*/
SELECT JSON_CONTAINS(json_info, '"张三"','$.name') FROM json_tab

/* one 只匹配第一个 */
select JSON_SEARCH(json_info,'one', '张三') from json_tab;
select JSON_SEARCH(json_info, 'one', '%朋友%',null,'$**.name') FROM json_tab
/* all查找所有的路径 */
select JSON_SEARCH(json_info,'all', '%朋友_', '朋', '$**.name') from json_tab;

/* 插入数据 */
INSERT into json_tab(json_info) VALUES ('[1,{"a":"b"},[2,"qwe"]]')

/* json数组指定位置上追加数据，path可以不是数组，结果一定是数据 */
/*在最后追加1这个元素*/
SELECT JSON_ARRAY_APPEND(json_info, '$', 1) FROM json_tab where id = 5
/* 在最前追加元素，下标从0开始*/
SELECT JSON_ARRAY_APPEND(json_info, '$[0]', 1) FROM json_tab where id = 4
/* 在最前追加元素，下标从0开始,在第二个元素，下标a中追加1的元素*/
SELECT JSON_ARRAY_APPEND(json_info, '$[1].a', 1) FROM json_tab where id = 4

/*json位置插入值，已存在的path忽略*/
SELECT JSON_INSERT(json_info, '$[1].b', 'bbbb') FROM json_tab where id = 4
SELECT JSON_INSERT(json_info, '$[3]', 'aaa') FROM json_tab where id = 4

/*json指定位置设置值，已存在的替换，不存在的新增*/
SELECT JSON_SET(json_info,'$[1].b','bbb') FROM json_tab where id = 4
select JSON_SET(json_info, '$[2]', 'aaa') from json_tab where id=4;
SELECT JSON_SET(json_info, '$[3]', 'ccc') FROM json_tab where id = 4


# json指定位置替换值，已存在的替换，不存在的忽略，类似map.replace
# JSON_REPLACE(json_doc, path, val[, path, val] ...)
select JSON_REPLACE(json_info, '$[2]', 'aaa') from json_tab where id=4;
select JSON_REPLACE(json_info, '$[3]', 'aaa') from json_tab where id=4;
# 删除指定位置元素
# JSON_REMOVE(json_doc, path[, path] ...)
select JSON_REMOVE(json_info, '$[1].a') from json_tab where id=4;
select JSON_REMOVE(json_info, '$[2]') from json_tab where id=4;