# BcSpaSample plugin for baserCMS

ucmitz の REST API と vue.js を利用する場合のサンプルアプリケーションです。

管理画面より、BcSpaSample プラグインをインストールし、次のURLにアクセスすることで、管理者ログインとユーザー管理を確認することができます。

https://localhost/bc_spa_sample/admin.html

　
## Installation

You can install this plugin into your baserCMS application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require baserproject/BcSpaSample
```



　
## ソースを確認する

ソースファイルは、 `/plugins/BcSpaSample/webroot/src/` に配置しています。

　
## コンパイル

webpack を使ってコンパイルします。 `/plugins/BcSpaSample/` に移動し次のコマンドを実行してください。

```javascript
npm install
npm run dev
```

　
## REST API

ログイン以外のリクエストヘッダーには、 `Authorization` をキーとしてJWT形式のアクセストークンが必要となります。

　
### ログイン

#### メソッド

```javascript
POST
```

#### URL

```javascript
/baser/api/baser-core/users/login.json
```

#### レスポンス

```javascript
{ 
    "access_token":"YOUR_ACCESS_TOKEN", 
    "refresh_token":"YOUR_REFRESH_TOKEN" 
}
```

#### コード例
```javascript
axios.post('/baser/api/baser-core/users/login.json', {
    email: this.name,
    password: this.password
}).then(function (response) {
    if (response.data) {
        this.$emit('set-login', response.data.access_token, response.data.refresh_token)
        this.$router.push('user_index')
    }
}.bind(this))
.catch(function (error) {
    this.isError = true
    if(error.response.status === 401) {
        this.message = 'アカウント名、パスワードが間違っています。'
    } else {
        this.message = error.response.data.message
    }
}.bind(this))
```

　
### トークンリフレッシュ

#### メソッド

```javascript
GET
```

#### URL

```javascript
/baser/api/baser-core/users/refresh_token.json
```

#### レスポンス

```javascript
{ 
    "access_token":"YOUR_ACCESS_TOKEN", 
    "refresh_token":"YOUR_REFRESH_TOKEN" 
}
```

#### コード例
```javascript
await axios.get('/baser/api/baser-core/users/refresh_token.json', {
    headers: {"Authorization": refreshToken},
    data: {}
}).then(function (response) {
    if (response.data) {
        this.setToken(response.data.access_token, response.data.refresh_token)
    } else {
        this.$router.push('/')
    }
}.bind(this))
.catch(function (error) {
    if (error.response.status === 401) {
        localStorage.refreshToken = ''
    }
})
```

　
### ユーザー一覧取得

#### メソッド

```javascript
GET
```

#### URL

```javascript
/baser/api/baser-core/users/index.json
```

#### リクエストパラメーター

| パラメーター名 | 内容                         | 
| -------------- | ---------------------------- | 
| num            | 取得件数                     | 
| user_group_id  | ユーザーグループID           | 
| name           | アカウント名（あいまい検索） | 


#### レスポンス

```javascript
{
	users: [
        {
            "id": 1,
            "name": "admin",
            "real_name_1": "admin",
            "real_name_2": "admin",
            "email": "test@example.com",
            "nickname": "ニックネーム",
            "user_groups": [
                "id": 1,
                "name": "admins",
                "title": "システム管理者",
                "auth_prefix": "admin",
                "default_favorites": "YTo3OntpOjA7YToyOntzOjQ6Im5hbWUiO3M6MjE6IuOCs+ODs+ODhuODs+ODhOeuoeeQhiI7czozOiJ1cmwiO3M6MjE6Ii9hZG1pbi9jb250ZW50cy9pbmRleCI7fWk6MTthOjI6e3M6NDoibmFtZSI7czoxODoi5paw552A5oOF5aCx566h55CGIjtzOjM6InVybCI7czozMDoiL2FkbWluL2Jsb2cvYmxvZ19wb3N0cy9pbmRleC8xIjt9aToyO2E6Mjp7czo0OiJuYW1lIjtzOjMwOiLmlrDnnYDmg4XloLHjgrPjg6Hjg7Pjg4jkuIDopqciO3M6MzoidXJsIjtzOjMzOiIvYWRtaW4vYmxvZy9ibG9nX2NvbW1lbnRzL2luZGV4LzEiO31pOjM7YToyOntzOjQ6Im5hbWUiO3M6MjQ6IuOBiuWVj+OBhOWQiOOCj+OBm+ioreWumiI7czozOiJ1cmwiO3M6MzE6Ii9hZG1pbi9tYWlsL21haWxfZmllbGRzL2luZGV4LzEiO31pOjQ7YToyOntzOjQ6Im5hbWUiO3M6MjQ6IuOBiuWVj+OBhOWQiOOCj+OBm+S4gOimpyI7czozOiJ1cmwiO3M6MzM6Ii9hZG1pbi9tYWlsL21haWxfbWVzc2FnZXMvaW5kZXgvMSI7fWk6NTthOjI6e3M6NDoibmFtZSI7czoyNDoi44Ki44OD44OX44Ot44O844OJ566h55CGIjtzOjM6InVybCI7czozMToiL2FkbWluL3VwbG9hZGVyL3VwbG9hZGVyX2ZpbGVzLyI7fWk6NjthOjI6e3M6NDoibmFtZSI7czoxNToi44Kv44Os44K444OD44OIIjtzOjM6InVybCI7czoyMDoiamF2YXNjcmlwdDpjcmVkaXQoKTsiO319",
                "use_move_contents": 1
            ]    
        },
        ...
    ]
} 
```

#### コード例
```javascript
axios.get('/baser/api/baser-core/users/index.json', {
    headers: {"Authorization": this.accessToken},
    data: {}
}).then(function (response) {
    if (response.data) {
        this.users = response.data.users
    } else {
        this.$router.push('/')
    }
}.bind(this))
```

### BcSpaSampleでtypescriptを利用する

##### 変更点

単一のvueファイルをtemplate(vueファイル)とscript(tsファイル)に分離

例)Login.vue→scriptの箇所をLogin.tsに移行
```html
<script lang="ts" src="./Login.ts"></script>
```
tsファイル内でVue.extendを使用することで、Vueコンポーネントの記述に近い書き方でTypeScriptを書くことができます。
```vue
// *.vue
export default {

    data: function () {...}
```
↓
```vue
// *.ts
export default Vue.extend({
    data: () => {
```
##### 型の定義

typeまたはinterfaceを使って、型を定義できます。
代入される変数や返り値が型とそぐわない場合TypeErrorが返ります。

```ts
type DataType = {
    message? : string,
};
// dataメソッドが返す値がstringもしくはundefinedという定義が出来る
data: (): DataType => {
    return {
        message : undefined,
    }
},
// TypeErrorの場合 文字列定義の変数に数値は定義できません
// Type 'number' is not assignable to type 'string'.ts(2322)
this.message = 100; // (property) message?: string | undefined
```

##### 型の再利用

main.tsにてUserタイプを定義してるので、importして再利用できます。

```ts
// main.ts
export type User = {
    ...
};
// form内で使用されるthis.userがUserタイプだと定義
// Form.ts
import { User } from '../../main';
type DataType = {
    user: User,
};
// index内で使用されるthis.usersがUserタイプを複数持つ配列だと定義
// user/Index.ts
import { User } from '../../main';
type DataType = {
    users?: User[],
};
```



### その他のAPI

その他のAPIは `webroot/src/` ディレクトリ内のサンプルコードを参考にしてください。


