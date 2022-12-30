<?php
namespace App\Http\Traits;

use Exception;

trait MessageTrait{

    public function msgInclude($object)
    {
        return response()->json([
            ['message' => 'Registro incluso com sucesso.'],
            $object
        ], 201);
    }

    public function msgUpdated(&$object)
    {
        return response()->json([
            ['message' => 'Registro atualizado com sucesso.'],
            $object
        ], 201);
    }

    public function msgRecordNotFound()
    {
        return response()->json([
            'message' => "Registro não encontrado.",
        ], 401);
    }

    public function msgNotAuthorized()
    {
        return response()->json([
            'message' => 'Recurso não autorizado.'
        ], 403);
    }

    public function msgResourceNotExists()
    {
        return response()->json([
            'message' => 'Recurso não existe'
        ], 403);
    }

    public function msgDeleted()
    {
        return response()->json([
            'message' => 'Registro excluído.'
        ], 201);
    }

    public function msgGeneric($msg, string $textMsg = null)
    {
        if ($textMsg == null)
            return response()->json([
                'message' => $msg
            ], 201);

        return response()->json([
           'message' => $textMsg,
           'record' => $msg,
        ], 201);
    }

    public function msgNotDeleted(Exception $exception)
    {
        return response()->json([
            'message' => 'Registro não excluído por estar em uso relacionado a outras tabelas no sistema.',
            'erro' => $exception->getMessage()
        ], 403);
    }

    public function msgSuccess(string $msg = '')
    {
        return response()->json([
            'message' => "{$msg}"
        ], 201);
    }

    public function msgMissingValidator(&$validator = null)
    {
        return response()->json([
            ['message' => "Algum campo de preenchimento obrigatório está faltando ou está com preenchimento inválido."],
            $validator->errors()
        ], 403);
    }

    public function msgDuplicatedField(string $fieldName = null, &$object = null)
    {
        return response()->json([
            ['message' => "Gravação cancelada. O valor informado para '{$fieldName}' está em duplicidade com outro registro.."],
            $object
        ], 403);
    }

    public function msgNotHasField(string $fieldName = null)
    {
        return response()->json([
            'message' => "Informe no método POST '{$fieldName}'."
        ], 403);
    }

}
?>
