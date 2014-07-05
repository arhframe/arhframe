<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Schema\Synchronizer;

use Doctrine\DBAL\Schema\Schema;

/**
 * The synchronizer knows how to synchronize a schema with the configured
 * database.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
interface SchemaSynchronizer
{
    /**
     * Get the SQL statements that can be executed to create the schema.
     *
     * @param  Schema $createSchema
     * @return array
     */
    public function getCreateSchema(Schema $createSchema);

    /**
     * Get the SQL Statements to update given schema with the underlying db.
     *
     * @param  Schema $toSchema
     * @param  bool   $noDrops
     * @return array
     */
    public function getUpdateSchema(Schema $toSchema, $noDrops = false);

    /**
     * Get the SQL Statements to drop the given schema from underlying db.
     *
     * @param  Schema $dropSchema
     * @return array
     */
    public function getDropSchema(Schema $dropSchema);

    /**
     * Get the SQL statements to drop all schema assets from underlying db.
     *
     * @return array
     */
    public function getDropAllSchema();

    /**
     * Create the Schema
     *
     * @param  Schema $createSchema
     * @return void
     */
    public function createSchema(Schema $createSchema);

    /**
     * Update the Schema to new schema version.
     *
     * @param  Schema $toSchema
     * @param  bool   $noDrops
     * @return void
     */
    public function updateSchema(Schema $toSchema, $noDrops = false);

    /**
     * Drop the given database schema from the underlying db.
     *
     * @param  Schema $dropSchema
     * @return void
     */
    public function dropSchema(Schema $dropSchema);

    /**
     * Drop all assets from the underyling db.
     *
     * @return void
     */
    public function dropAllSchema();
}
